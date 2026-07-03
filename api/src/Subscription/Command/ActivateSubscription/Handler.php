<?php

declare(strict_types=1);

namespace App\Subscription\Command\ActivateSubscription;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Subscription\Id;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\Subscription;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
use DomainException;

/**
 * Обработчик команды ActivateSubscription (User Subscription).
 *
 * Инвариант: один пользователь — одна активная подписка.
 */
final readonly class Handler
{
    public function __construct(
        private SubscriptionRepository $subscriptions,
        private Flusher $flusher
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): Id
    {
        $userId = new UserId($command->userId);

        if ($this->subscriptions->hasActiveByUserId($userId)) {
            throw new DomainException('User already has an active subscription.');
        }

        $subscription = Subscription::activate(
            Id::generate(),
            $userId,
            Plan::from($command->planId),
            Period::fromDurationDays($command->durationDays),
        );

        $this->subscriptions->add($subscription);
        $this->flusher->flush();

        return $subscription->getId();
    }
}
