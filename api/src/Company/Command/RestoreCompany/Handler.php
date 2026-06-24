<?php

declare(strict_types=1);

namespace App\Company\Command\RestoreCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\UserId;
use App\Company\Exception\AccessDeniedException;
use App\Infrastructure\Doctrine\Flusher;

/**
 * Обработчик команды RestoreCompany.
 */
final readonly class Handler
{
    public function __construct(
        private CompanyRepository $companies,
        private Flusher           $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $company = $this->companies->get(new Id($command->id));

        // Проверяем права: только владелец может восстановить компанию
        if (!$company->getUserId()->isEqualTo(new UserId($command->userId))) {
            throw new AccessDeniedException();
        }

        $company->restore();

        $this->flusher->flush();
    }
}
