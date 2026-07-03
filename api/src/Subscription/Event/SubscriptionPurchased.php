<?php

declare(strict_types=1);

namespace App\Subscription\Event;

use App\Subscription\Entity\Subscription\Id;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;
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
        public readonly Id $id,
        public readonly UserId $userId,
        public readonly Plan $plan,
        public readonly Period $period,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
