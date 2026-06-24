<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompanyStats;

/**
 * Query DTO: Запрос на получение статистики компании (CQRS Read-side).
 */
final readonly class Query
{
    public function __construct(
        public string $companyId,
        public string $userId,
    ) {}
}
