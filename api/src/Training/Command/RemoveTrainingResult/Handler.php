<?php

declare(strict_types=1);

namespace App\Training\Command\RemoveTrainingResult;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id as CompanyId;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Infrastructure\Doctrine\Flusher;
use App\Training\Entity\Record\Id as RecordId;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Training\Exception\AccessDeniedException;
use DomainException;

final class Handler
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private TrainingRecordRepository $records,
        private CompanyRepository $companies,
        private Flusher $flusher,
    ) {}

    public function handle(Command $command): void
    {
        $company = $this->companies->get(new CompanyId($command->companyId));

        if (!$company->getUserId()->isEqualTo(new CompanyUserId($command->userId))) {
            throw new AccessDeniedException();
        }

        $record = $this->records->find(new RecordId($command->id));

        if (!$record) {
            throw new DomainException('Record not found.');
        }

        $this->records->removeRecord($record);

        $this->flusher->flush();
    }
}
