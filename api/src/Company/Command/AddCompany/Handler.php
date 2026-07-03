<?php

declare(strict_types=1);

namespace App\Company\Command\AddCompany;

use App\Company\Entity\Company\Company;
use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Entity\Company\UserId;
use App\Infrastructure\Doctrine\Flusher;
use DomainException;

/**
 * Обработчик команды AddCompany.
 *
 * Единственный handler для данной команды (1 команда → 1 handler).
 *
 * Ответственность:
 *  1. Трансформация примитивов из Command → Value Objects (инварианты проверяются автоматически)
 *  2. Проверка бизнес-уникальности ИНН
 *  3. Создание агрегата через фабричный метод (внутри записывается CompanyAdded с userId)
 *  4. Сохранение агрегата (транзакционная граница)
 *  5. Сброс (flush) через Flusher
 */
final class Handler
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly Flusher $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $id     = new Id($command->id);
        $name   = Name::fromString($command->name);
        $inn    = Inn::fromString($command->inn);
        $userId = new UserId($command->userId);

        if ($this->companies->hasByInn($inn)) {
            throw new DomainException('Company with this INN already exists.');
        }

        $company = Company::create($id, $name, $inn, $userId);

        $this->companies->add($company);

        $this->flusher->flush();
    }
}
