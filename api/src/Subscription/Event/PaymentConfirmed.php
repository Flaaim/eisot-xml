<?php

declare(strict_types=1);

namespace App\Subscription\Event;

use DateTimeImmutable;

/**
 * Доменное событие: платёж User Subscription подтверждён.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class PaymentConfirmed
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $paymentId,
        public readonly string $userId,
        public readonly string $plan,
        public readonly int $durationDays,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
