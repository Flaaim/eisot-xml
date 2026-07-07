<?php

declare(strict_types=1);

namespace App\Subscription\Event;

use DateTimeImmutable;

/**
 * Доменное событие: User Subscription оформлена.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class SubscriptionPurchased
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $plan,
        public readonly string $ended,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
