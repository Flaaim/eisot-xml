<?php

declare(strict_types=1);

namespace App\Worker\Test\Builder;

use App\Worker\Entity\Worker\CompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\Worker;
use App\Worker\Entity\Worker\WorkerId;
use Ramsey\Uuid\Uuid;

/** @psalm-api */
final class WorkerBuilder
{
    private WorkerId $id;
    private CompanyId $companyId;
    private FullName $fullName;
    private Profession $profession;
    private SnilsInfo $snilsInfo;

    public function __construct()
    {
        $this->id         = WorkerId::generate();
        $this->companyId  = new CompanyId(Uuid::uuid4()->toString());
        $this->fullName   = FullName::create('Иванов', 'Иван', 'Иванович');
        $this->profession = Profession::fromString('Слесарь');
        $this->snilsInfo  = SnilsInfo::forCitizen(Snils::fromString('112-233-445 95'));
    }

    public function withId(WorkerId $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withCompanyId(CompanyId $companyId): self
    {
        $clone = clone $this;
        $clone->companyId = $companyId;
        return $clone;
    }

    public function withFullName(FullName $fullName): self
    {
        $clone = clone $this;
        $clone->fullName = $fullName;
        return $clone;
    }

    public function withProfession(Profession $profession): self
    {
        $clone = clone $this;
        $clone->profession = $profession;
        return $clone;
    }

    public function withSnilsInfo(SnilsInfo $snilsInfo): self
    {
        $clone = clone $this;
        $clone->snilsInfo = $snilsInfo;
        return $clone;
    }

    public function build(): Worker
    {
        return Worker::register(
            $this->id,
            $this->companyId,
            $this->fullName,
            $this->profession,
            $this->snilsInfo,
        );
    }
}
