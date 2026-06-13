<?php

declare(strict_types=1);

namespace App\Company\Command\ArchiveCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\UserId;
use App\Company\Exception\AccessDeniedException;
use App\Infrastructure\Doctrine\Flusher;

/**
 * Обработчик команды ArchiveCompany.
 *
 * Ответственность:
 *  1. Поиск агрегата по ID (DomainException если не найден)
 *  2. Проверка: команду инициировал владелец компании
 *  3. Делегирование бизнес-логики агрегату (archive() проверяет инварианты + записывает событие)
 *  4. Сброс (flush) через Flusher
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

        // Проверяем право на изменение: только владелец может архивировать компанию
        if (!$company->getUserId()->isEqualTo(new UserId($command->userId))) {
            throw new AccessDeniedException();
        }

        $company->archive();

        $this->flusher->flush();
    }
}
