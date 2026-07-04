<?php

declare(strict_types=1);

namespace App\Company\ReadModel;

/**
 * Read-модель для получения данных о компаниях.
 *
 * Интерфейс определён в слое Domain/ReadModel — реализация в Query-слое через DBAL.
 */
interface CompanyFetcherInterface
{
    /**
     * @return list<array{id: string, name: string, inn: string, status: string, workers_count: int, protocols_count: int}>
     */
    public function findAllByUserId(string $userId): array;
    public function findOneByUserId(string $id, string $userId): array;
}
