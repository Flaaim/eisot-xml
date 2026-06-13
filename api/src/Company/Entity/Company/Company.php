<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use App\Company\Event\CompanyAdded;
use App\Company\Event\CompanyArchived;
use App\Company\Event\CompanyInnChanged;
use App\Company\Event\CompanyRenamed;
use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'companies')]
final class Company implements AggregateRoot
{
    use EventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'company_id')]
        private Id $id,
        #[ORM\Column(type: 'company_name')]
        private Name $name,
        #[ORM\Column(type: 'company_inn')]
        private Inn $inn,
        #[ORM\Column(type: 'boolean', options: ['default' => false])]
        private bool $isArchived = false,
    ) {
    }

    public static function create(
        Id $id,
        Name $name,
        Inn $inn,
    ): self {
        $company = new self($id, $name, $inn);

        $company->recordEvent(new CompanyAdded($id, $name, $inn));

        return $company;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getInn(): Inn
    {
        return $this->inn;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function rename(Name $newName): void
    {
        if ($this->name->isEqualTo($newName)) {
            throw new \DomainException('Company already has this name.');
        }

        $this->name = $newName;

        $this->recordEvent(new CompanyRenamed($this->id, $newName));
    }

    public function changeInn(Inn $newInn): void
    {
        if ($this->inn->isEqualTo($newInn)) {
            throw new \DomainException('Company already has this INN.');
        }

        $this->inn = $newInn;

        $this->recordEvent(new CompanyInnChanged($this->id, $newInn));
    }

    /**
     * Переводит компанию в архив (мягкое удаление).
     *
     * Инвариант: нельзя архивировать уже архивированную компанию.
     */
    public function archive(): void
    {
        if ($this->isArchived) {
            throw new \DomainException('Company is already archived.');
        }

        $this->isArchived = true;

        $this->recordEvent(new CompanyArchived($this->id));
    }
}
