<?php

declare(strict_types=1);

namespace App\Subscription\Query\GetPaymentStatus;

use App\Subscription\Entity\Payment\Id;
use App\Subscription\Entity\Payment\PaymentRepository;
use App\Subscription\Entity\Subscription\UserId;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private PaymentRepository $payments,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Query $query): PaymentStatusDTO
    {
        $payment = $this->payments->findByIdAndUserId(
            new Id($query->paymentId),
            new UserId($query->userId),
        );

        if (null === $payment) {
            throw new DomainException('Payment not found.');
        }

        return new PaymentStatusDTO(
            paymentId: $payment->getId()->getValue(),
            status: $payment->getStatus()->value,
            planId: $payment->getPlan()->value,
        );
    }
}
