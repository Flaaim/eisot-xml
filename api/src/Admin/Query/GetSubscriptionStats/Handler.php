<?php

declare(strict_types=1);

namespace App\Admin\Query\GetSubscriptionStats;

use App\Admin\ReadModel\AdminUserFetcherInterface;

final readonly class Handler
{
    public function __construct(
        private AdminUserFetcherInterface $fetcher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod, UnusedParam */
    public function handle(Query $query): SubscriptionStatsDTO
    {
        $stats = $this->fetcher->getSubscriptionStats();

        return new SubscriptionStatsDTO(
            totalUsers: $stats['total_users'],
            registrationsLast30Days: $stats['registrations_last_30_days'],
            activeSubscriptions: $stats['active_subscriptions'],
            activeBasicPlan: $stats['active_basic_plan'],
            activeExtendedPlan: $stats['active_extended_plan'],
            activeSubscriptionsLast30Days: $stats['active_subscriptions_last_30_days'],
        );
    }
}
