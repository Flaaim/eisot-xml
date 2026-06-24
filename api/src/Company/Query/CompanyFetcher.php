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
     * @return list<array{id: string, name: string, inn: string, status: string, workers_count: int, protocols_count: int}>
     */
    public function findAllByUserId(string $userId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $result = $qb
            ->select(
                'c.id',
                'c.name',
                'c.inn',
                'c.status',
                '(SELECT COUNT(w.id) FROM workers w WHERE w.company_id = c.id) AS workers_count',
                '(SELECT COUNT(tr.id) FROM training_records tr JOIN workers w ON tr.worker_id = w.id WHERE w.company_id = c.id) AS protocols_count'
            )
            ->from('companies', 'c')
            ->where('c.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy("CASE WHEN c.status = 'ACTIVE' THEN 0 ELSE 1 END", 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->executeQuery();

        /** @var list<array{id: string, name: string, inn: string, status: string, workers_count: int, protocols_count: int}> */
        return $result->fetchAllAssociative();
    }

    public function findOneByUserId(string $id, string $userId): array
    {
        $qb = $this->connection->createQueryBuilder();
        $result = $qb
            ->select('id', 'name', 'inn', 'status')
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
