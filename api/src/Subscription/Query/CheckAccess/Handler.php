<?php

declare(strict_types=1);

namespace App\Subscription\Query\CheckAccess;

use App\Auth\Entity\User\Id as AuthUserId;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;

/**
 * Проверяет Active Status User Subscription перед генерацией XML.
 */
final readonly class Handler
{
    public function __construct(
        private SubscriptionRepository $subscriptions,
        private UserRepository $users,
        private Flusher $flusher,
    ) {}

    public function handle(Query $query): AccessDTO
    {
        $user = $this->users->get(new AuthUserId($query->userId));
        $trialUsed = $user->isTrialUsed();

        $subscription = $this->subscriptions->findActiveByUserId(
            new UserId($query->userId),
        );

        if (null === $subscription) {
            $this->flusher->flush();

            return new AccessDTO(
                hasAccess: false,
                plan: null,
                status: null,
                periodStart: null,
                periodEnd: null,
                trialUsed: $trialUsed,
                trialAvailable: !$trialUsed,
            );
        }

        $this->flusher->flush();

        $period = $subscription->getPeriod();

        return new AccessDTO(
            hasAccess: true,
            plan: $subscription->getPlan()->value,
            status: $subscription->getStatus()->value,
            periodStart: $period->getStartDate()->format('Y-m-d'),
            periodEnd: $period->getEndDate()->format('Y-m-d'),
            trialUsed: $trialUsed,
            trialAvailable: false,
        );
    }
}
