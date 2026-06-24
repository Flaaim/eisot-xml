<?php

declare(strict_types=1);

namespace App\Company\Query;

use App\Company\Entity\Company\Company;
use App\Company\Query\GetCompanies\CompanyShortDTO;
use App\Company\ReadModel\CompanyFetcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

/**
 * DBAL-реализация CompanyFetcherInterface.
 *
 * Обращается напрямую к таблице `companies` без загрузки агрегатов (CQRS read-side).
 */
final class CompanyFetcher implements CompanyFetcherInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    /**
     * @throws Exception
     *
     * @return list<array{id: string, name: string, inn: string}>
     */
    public function findAllByUserId(string $userId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $result = $qb
            ->select('id', 'name', 'inn', 'is_archived')
            ->from('companies')
            ->where('user_id = :userId')
            ->setParameter('userId', $userId)
            ->executeQuery();

        /** @var list<array{id: string, name: string, inn: string}> */
        return $result->fetchAllAssociative();
    }

    public function findOneByUserId(string $id, string $userId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb
            ->select('id', 'name', 'inn', 'is_archived')
            ->from('companies')
            ->where('id = :id')
            ->andWhere('user_id = :userId')
            ->setParameter('id', $id)
            ->setParameter('userId', $userId)
            ->executeQuery();

        $result = $result->fetchAssociative();

        if(!$result) return [];
        return $result;
    }
}
