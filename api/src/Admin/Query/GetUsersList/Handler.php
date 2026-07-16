<?php

declare(strict_types=1);

namespace App\Admin\Query\GetUsersList;

use App\Admin\ReadModel\AdminUserFetcherInterface;

final readonly class Handler
{
    public function __construct(
        private AdminUserFetcherInterface $fetcher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Query $query): UsersListResult
    {
        $offset = ($query->page - 1) * $query->limit;

        $rows = $this->fetcher->findUsers(
            $query->limit,
            $offset,
            $query->email,
            $query->subscriptionStatus,
        );

        $items = array_map(
            static fn (array $row): UserSummaryDTO => new UserSummaryDTO(
                id: $row['id'],
                email: $row['email'],
                status: $row['status'],
                role: $row['role'],
                createdAt: $row['created_at'],
                activeSubscriptionPlan: $row['active_subscription_plan'],
                subscriptionStatus: $row['subscription_status'],
                companiesCount: $row['companies_count'],
            ),
            $rows,
        );

        return new UsersListResult(
            items: $items,
            total: $this->fetcher->countUsers($query->email, $query->subscriptionStatus),
            page: $query->page,
            limit: $query->limit,
        );
    }
}
