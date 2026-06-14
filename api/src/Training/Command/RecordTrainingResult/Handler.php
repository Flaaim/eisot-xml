<?php

declare(strict_types=1);

namespace App\Training\Command\RecordTrainingResult;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Infrastructure\Doctrine\Flusher;
use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\Program;
use App\Training\Entity\Record\ProtocolNumber;
use App\Training\Entity\Record\Result;
use App\Training\Entity\Record\TrainingRecord;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Training\Entity\Record\WorkerId;
use App\Training\Exception\AccessDeniedException;
use App\Worker\Entity\Worker\WorkerId as WorkerEntityId;
use App\Worker\Entity\Worker\WorkerRepository;

/**
 * Обработчик: зафиксировать результат обучения.
 *
 * Цепочка авторизации: userId → Company.userId → Company ← Worker.companyId → Worker ← TrainingRecord.workerId
 */
final class Handler
{
    public function __construct(
        private readonly WorkerRepository          $workers,
        private readonly CompanyRepository         $companies,
        private readonly TrainingRecordRepository   $records,
        private readonly Flusher                    $flusher,
    ) {}

    public function handle(Command $command): void
    {
        // 1. Загрузить Worker
        $worker = $this->workers->get(new WorkerEntityId($command->workerId));

        // 2. Загрузить Company → проверить userId
        $company = $this->companies->get(
            new \App\Company\Entity\Company\Id($worker->getCompanyId()->getValue())
        );

        if (!$company->getUserId()->isEqualTo(new CompanyUserId($command->userId))) {
            throw new AccessDeniedException();
        }

        // 3. Парсить дату
        $date = \DateTimeImmutable::createFromFormat('d.m.Y', $command->date);
        if ($date === false) {
            throw new \DomainException('Invalid date format. Expected: d.m.Y (e.g. 28.09.2023).');
        }

        // 4. Создать Value Objects
        $record = TrainingRecord::record(
            new Id($command->id),
            new WorkerId($command->workerId),
            Program::fromId($command->program),
            Result::fromString($command->result),
            $date,
            ProtocolNumber::fromString($command->protocolNumber),
        );

        // 5. Сохранить
        $this->records->add($record);
        $this->flusher->flush();
    }
}
