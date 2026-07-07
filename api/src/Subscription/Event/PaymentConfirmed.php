<?php

declare(strict_types=1);

namespace App\Subscription\Event;

use App\Subscription\Entity\Payment\Id;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;
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
        public readonly Id $paymentId,
        public readonly UserId $userId,
        public readonly Plan $plan,
        public readonly int $durationDays,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
