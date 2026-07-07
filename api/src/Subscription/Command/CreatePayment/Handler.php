<?php

declare(strict_types=1);

namespace App\Subscription\Command\CreatePayment;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\Id;
use App\Subscription\Entity\Payment\Payment;
use App\Subscription\Entity\Payment\PaymentRepository;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Service\PaymentGatewayInterface;
use App\Subscription\Service\PlanPricing;

final readonly class Handler
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private PaymentRepository $payments,
        private Flusher $flusher
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): Result
    {
        $userId = new UserId($command->userId);
        $plan = Plan::from($command->planId);
        $durationDays = PlanPricing::resolve($plan, $command->durationDays);
        $amount = PlanPricing::amountFor($plan);
        $paymentId = Id::generate();

        $returnUrl = $this->appendQueryParameter(
            $command->returnUrl,
            'paymentId',
            $paymentId->getValue(),
        );

        $gatewayResult = $this->paymentGateway->createPayment(
            $userId,
            $plan,
            $amount,
            $durationDays,
            $returnUrl,
        );

        $payment = Payment::createPending(
            $paymentId,
            new ExternalId($gatewayResult->externalId),
            $userId,
            $plan,
            $amount,
            $durationDays,
        );

        $this->payments->add($payment);
        $this->flusher->flush();

        return new Result(
            paymentId: $payment->getId()->getValue(),
            confirmationUrl: $gatewayResult->confirmationUrl,
        );
    }

    private function appendQueryParameter(string $url, string $key, string $value): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . rawurlencode($key) . '=' . rawurlencode($value);
    }
}
