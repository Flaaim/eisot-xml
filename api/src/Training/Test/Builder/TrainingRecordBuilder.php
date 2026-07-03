<?php

declare(strict_types=1);

namespace App\Training\Test\Builder;

use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\Program;
use App\Training\Entity\Record\ProtocolNumber;
use App\Training\Entity\Record\Result;
use App\Training\Entity\Record\TrainingRecord;
use App\Training\Entity\Record\WorkerId;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class TrainingRecordBuilder
{
    private Id $id;
    private WorkerId $workerId;
    private Program $program;
    private Result $result;
    private DateTimeImmutable $date;
    private ProtocolNumber $protocolNumber;

    public function __construct()
    {
        $this->id             = Id::generate();
        $this->workerId       = new WorkerId(Uuid::uuid4()->toString());
        $this->program        = Program::fromId(1);
        $this->result         = Result::satisfactory();
        $this->date           = new DateTimeImmutable('2023-09-28 16:56:01');
        $this->protocolNumber = ProtocolNumber::fromString('ПР-001/2023');
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withWorkerId(WorkerId $workerId): self
    {
        $clone = clone $this;
        $clone->workerId = $workerId;
        return $clone;
    }

    public function withProgram(Program $program): self
    {
        $clone = clone $this;
        $clone->program = $program;
        return $clone;
    }

    public function withResult(Result $result): self
    {
        $clone = clone $this;
        $clone->result = $result;
        return $clone;
    }

    public function withDate(DateTimeImmutable $date): self
    {
        $clone = clone $this;
        $clone->date = $date;
        return $clone;
    }

    public function withProtocolNumber(ProtocolNumber $protocolNumber): self
    {
        $clone = clone $this;
        $clone->protocolNumber = $protocolNumber;
        return $clone;
    }

    public function build(): TrainingRecord
    {
        return TrainingRecord::record(
            $this->id,
            $this->workerId,
            $this->program,
            $this->result,
            $this->date,
            $this->protocolNumber,
        );
    }
}
