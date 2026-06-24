<?php

declare(strict_types=1);

namespace App\Training\Query\GetRegistryRecords;

/**
 * Query DTO: запрос на получение реестра записей обучения.
 */
final readonly class Query
{
    public function __construct(
        public string $companyId,
        public string $userId,
    ) {}
}
