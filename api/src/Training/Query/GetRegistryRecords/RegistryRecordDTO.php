<?php

declare(strict_types=1);

namespace App\Training\Query\GetRegistryRecords;

/**
 * RegistryRecordDTO: элемент плоского списка реестра обучения.
 */
final readonly class RegistryRecordDTO
{
    public function __construct(
        public string $id,
        public string $workerFullName,
        public string $snils,
        public string $profession,
        public string $programTitle,
        public string $result,
        public string $date,
        public string $protocolNumber,
    ) {}
}
