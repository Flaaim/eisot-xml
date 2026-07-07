<?php

declare(strict_types=1);

namespace App\Subscription\Command\FailPayment;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\PaymentRepository;

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
            return;
        }

        $payment->fail();
        $this->flusher->flush();
    }
}
