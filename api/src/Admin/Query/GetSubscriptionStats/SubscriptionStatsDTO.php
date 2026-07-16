<?php

declare(strict_types=1);

namespace App\Admin\Query\GetSubscriptionStats;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class SubscriptionStatsDTO
{
    public function __construct(
        public int $totalUsers,
        public int $registrationsLast30Days,
        public int $activeSubscriptions,
        public int $activeBasicPlan,
        public int $activeExtendedPlan,
        public int $activeSubscriptionsLast30Days,
    ) {}
}
