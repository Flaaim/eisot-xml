<?php

declare(strict_types=1);

namespace App\Subscription\Query\CheckAccess;

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
        private Flusher $flusher,
    ) {}

    public function handle(Query $query): AccessDTO
    {
        $subscription = $this->subscriptions->findActiveByUserId(
            new UserId($query->userId),
        );

        if ($subscription === null) {
            $this->flusher->flush();

            return new AccessDTO(
                hasAccess: false,
                plan: null,
                status: null,
                periodStart: null,
                periodEnd: null,
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
        );
    }
}
