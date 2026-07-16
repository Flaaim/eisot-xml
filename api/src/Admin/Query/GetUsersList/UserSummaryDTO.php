<?php

declare(strict_types=1);

namespace App\Admin\Query\GetUsersList;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class UserSummaryDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $status,
        public string $role,
        public string $createdAt,
        public ?string $activeSubscriptionPlan,
        public ?string $subscriptionStatus,
        public int $companiesCount,
    ) {}
}
