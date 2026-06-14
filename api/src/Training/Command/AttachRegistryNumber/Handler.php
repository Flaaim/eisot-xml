<?php

declare(strict_types=1);

namespace App\Training\Command\AttachRegistryNumber;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Infrastructure\Doctrine\Flusher;
use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\RegistryNumber;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Training\Exception\AccessDeniedException;
use App\Worker\Entity\Worker\WorkerId as WorkerEntityId;
use App\Worker\Entity\Worker\WorkerRepository;

/**
 * Обработчик: прикрепить регистрационный номер к записи об обучении.
 *
 * Цепочка авторизации: TrainingRecord → Worker → Company → userId
 */
final class Handler
{
    public function __construct(
        private readonly TrainingRecordRepository $records,
        private readonly WorkerRepository         $workers,
        private readonly CompanyRepository        $companies,
        private readonly Flusher                  $flusher,
    ) {}

    public function handle(Command $command): void
    {
        // 1. Загрузить TrainingRecord
        $record = $this->records->get(new Id($command->recordId));

        // 2. Загрузить Worker → Company → проверить userId
        $worker = $this->workers->get(new WorkerEntityId($record->getWorkerId()->getValue()));

        $company = $this->companies->get(
            new \App\Company\Entity\Company\Id($worker->getCompanyId()->getValue())
        );

        if (!$company->getUserId()->isEqualTo(new CompanyUserId($command->userId))) {
            throw new AccessDeniedException();
        }

        // 3. Прикрепить номер (инвариант проверяется в агрегате)
        $record->attachRegistryNumber(RegistryNumber::fromString($command->registryNumber));

        $this->flusher->flush();
    }
}
