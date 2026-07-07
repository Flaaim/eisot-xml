<?php

declare(strict_types=1);

namespace App\Subscription\Command\ConfirmPayment;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\PaymentRepository;
use DomainException;

final readonly class Handler
{
    public function __construct(
        private PaymentRepository $payments,
        private Flusher $flusher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): void
    {
        $payment = $this->payments->findByExternalId(new ExternalId($command->externalId));

        if (null === $payment) {
            throw new DomainException('Payment not found.');
        }

        $payment->confirm();
        $this->flusher->flush();
    }
}
