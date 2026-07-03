<?php

declare(strict_types=1);

namespace App\Subscription\Event;

use App\Subscription\Entity\Subscription\Id;
use App\Subscription\Entity\Subscription\UserId;
use DateTimeImmutable;

/**
 * Доменное событие: User Subscription истекла.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class SubscriptionExpired
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
        public readonly UserId $userId,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
