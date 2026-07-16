<?php

declare(strict_types=1);

namespace App\Admin\ReadModel;

interface AdminUserFetcherInterface
{
    /**
     * @return list<array{
     *     id: string,
     *     email: string,
     *     status: string,
     *     role: string,
     *     created_at: string,
     *     active_subscription_plan: ?string,
     *     subscription_status: ?string,
     *     companies_count: int
     * }>
     */
    public function findUsers(int $limit, int $offset, ?string $email, ?string $subscriptionStatus): array;

    public function countUsers(?string $email, ?string $subscriptionStatus): int;

    /**
     * @return array{
     *     total_users: int,
     *     registrations_last_30_days: int,
     *     active_subscriptions: int,
     *     active_basic_plan: int,
     *     active_extended_plan: int,
     *     active_subscriptions_last_30_days: int
     * }
     */
    public function getSubscriptionStats(): array;
}
