<?php

declare(strict_types=1);

namespace App\Company\Command\ChangeCompanyInn;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Infrastructure\Doctrine\Flusher;
use DomainException;

/**
 * Обработчик команды ChangeCompanyInn.
 *
 * Ответственность:
 *  1. Поиск агрегата по ID (DomainException если не найден)
 *  2. Трансформация примитива → Value Object Inn (инварианты проверяются автоматически)
 *  3. Проверка уникальности нового ИНН (бизнес-правило)
 *  4. Вызов метода агрегата — проверка инвариантов + запись события CompanyInnChanged
 *  5. Сброс (flush) через Flusher
 */
final class Handler
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly Flusher           $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $company = $this->companies->get(new Id($command->id));

        $newInn = Inn::fromString($command->inn);

        if ($this->companies->hasByInn($newInn)) {
            throw new DomainException('Company with this INN already exists.');
        }

        $company->changeInn($newInn);

        $this->flusher->flush();
    }
}
