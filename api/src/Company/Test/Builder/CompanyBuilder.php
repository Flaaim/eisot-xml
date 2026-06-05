<?php

declare(strict_types=1);

namespace App\Company\Test\Builder;

use App\Company\Entity\Company\Company;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;

final class CompanyBuilder
{
    private Id $id;
    private Name $name;
    private Inn $inn;

    public function __construct()
    {
        $this->id   = Id::generate();
        $this->name = Name::fromString('ООО Рога и Копыта');
        $this->inn  = Inn::fromString('7707083893');
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withName(Name $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withInn(Inn $inn): self
    {
        $clone = clone $this;
        $clone->inn = $inn;
        return $clone;
    }

    public function build(): Company
    {
        return Company::create(
            $this->id,
            $this->name,
            $this->inn,
        );
    }
}
