<?php

declare(strict_types=1);

namespace App\Training\Query\ExportRegistryToXml;

/**
 * Query DTO: запрос на выгрузку выбранных записей обучения в XML-формат.
 */
final readonly class Query
{
    /**
     * @param string[] $recordIds
     */
    public function __construct(
        public array $recordIds,
        public string $userId,
    ) {}
}
