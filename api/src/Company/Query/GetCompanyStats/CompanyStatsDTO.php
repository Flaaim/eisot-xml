<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompanyStats;

/**
 * DTO Ответа: Статистика компании.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final readonly class CompanyStatsDTO
{
    public function __construct(
        public int $workersCount,
        public int $protocolsCount,
        public string $status = 'Активна',
    ) {}
}
