<?php

declare(strict_types=1);

namespace App\Admin\Query\GetUsersList;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class UsersListResult
{
    /**
     * @param list<UserSummaryDTO> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {}
}
