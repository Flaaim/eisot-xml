<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompanies;

use App\Company\ReadModel\CompanyFetcherInterface;

/**
 * Обработчик запроса GetCompanies.
 *
 * CQRS read-side: не поднимает агрегаты, работает напрямую через ReadModel (DBAL).
 * Маппит сырые ассоциативные массивы в типизированные CompanyShortDTO.
 */
final class Handler
{
    public function __construct(
        private readonly CompanyFetcherInterface $fetcher,
    ) {}

    /**
     * @return list<CompanyShortDTO>
     */
    public function handle(Query $query): array
    {
        $rows = $this->fetcher->findAllByUserId($query->userId);

        return array_map(
            static fn(array $row): CompanyShortDTO => new CompanyShortDTO(
                $row['id'],
                $row['name'],
                $row['inn'],
            ),
            $rows,
        );
    }
}
