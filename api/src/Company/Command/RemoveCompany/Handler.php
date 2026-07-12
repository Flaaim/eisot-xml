<?php

declare(strict_types=1);

namespace App\Company\Command\RemoveCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\UserId;
use App\Company\Exception\AccessDeniedException;
use App\Infrastructure\Doctrine\Flusher;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Worker\Entity\Worker\CompanyId as WorkerCompanyId;
use App\Worker\Entity\Worker\WorkerRepository;

/**
 * Обработчик команды RemoveCompany.
 *
 * Удаляет архивированную компанию и связанные записи работников и протоколов обучения.
 */
final readonly class Handler
{
    public function __construct(
        private CompanyRepository $companies,
        private WorkerRepository $workers,
        private TrainingRecordRepository $trainingRecords,
        private Flusher $flusher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): void
    {
        $company = $this->companies->get(new Id($command->id));

        if (!$company->getUserId()->isEqualTo(new UserId($command->userId))) {
            throw new AccessDeniedException();
        }

        $company->remove();

        $companyId = $company->getId()->getValue();

        $this->trainingRecords->deleteAllByCompanyId($companyId);
        $this->workers->deleteAllByCompanyId(new WorkerCompanyId($companyId));
        $this->companies->remove($company);

        $this->flusher->flush();
    }
}
