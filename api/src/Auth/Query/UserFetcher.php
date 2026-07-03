<?php

declare(strict_types=1);

namespace App\Auth\Query;

use App\Auth\ReadModel\UserFetcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/** @psalm-suppress UnusedClass */
final class UserFetcher implements UserFetcherInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function findDetail(string $id): ?array
    {
        $result = $this->connection->createQueryBuilder()
            ->select('id, email')
            ->from('users')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return false === $result ? null : $result;
    }
}
