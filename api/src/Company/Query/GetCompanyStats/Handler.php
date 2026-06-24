<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompanyStats;

use Doctrine\DBAL\Connection;

/**
 * Обработчик запроса GetCompanyStats.
 *
 * Выполняет прямые SQL-запросы к БД через DBAL (CQRS Read-Model) без поднятия ORM-сущностей.
 */
final readonly class Handler
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function handle(Query $query): CompanyStatsDTO
    {
        // Проверка существования компании и прав владельца
        $companyRow = $this->connection->createQueryBuilder()
            ->select('is_archived')
            ->from('companies')
            ->where('id = :companyId')
            ->andWhere('user_id = :userId')
            ->setParameter('companyId', $query->companyId)
            ->setParameter('userId', $query->userId)
            ->executeQuery()
            ->fetchAssociative();

        if ($companyRow === false) {
            throw new \DomainException('Company not found.');
        }

        $isArchived = (bool)$companyRow['is_archived'];
        $status = $isArchived ? 'В архиве' : 'Активна';

        // 1. SELECT COUNT(w.id) FROM workers w JOIN companies c ON w.company_id = c.id WHERE w.company_id = :companyId AND c.user_id = :userId
        $workersCount = (int)$this->connection->createQueryBuilder()
            ->select('COUNT(w.id)')
            ->from('workers', 'w')
            ->innerJoin('w', 'companies', 'c', 'w.company_id = c.id')
            ->where('w.company_id = :companyId')
            ->andWhere('c.user_id = :userId')
            ->setParameter('companyId', $query->companyId)
            ->setParameter('userId', $query->userId)
            ->executeQuery()
            ->fetchOne();

        // 2. SELECT COUNT(tr.id) FROM training_records tr JOIN workers w ON tr.worker_id = w.id WHERE w.company_id = :companyId
        $protocolsCount = (int)$this->connection->createQueryBuilder()
            ->select('COUNT(tr.id)')
            ->from('training_records', 'tr')
            ->innerJoin('tr', 'workers', 'w', 'tr.worker_id = w.id')
            ->where('w.company_id = :companyId')
            ->setParameter('companyId', $query->companyId)
            ->executeQuery()
            ->fetchOne();

        return new CompanyStatsDTO(
            workersCount: $workersCount,
            protocolsCount: $protocolsCount,
            status: $status
        );
    }
}
