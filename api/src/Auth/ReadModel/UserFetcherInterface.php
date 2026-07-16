<?php

declare(strict_types=1);

namespace App\Auth\ReadModel;

interface UserFetcherInterface
{
    /**
     * @return array{id: string, email: string, role: string}|null
     */
    public function findDetail(string $id): ?array;
}
