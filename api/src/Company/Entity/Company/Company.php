<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use App\Company\Event\CompanyAdded;
use App\Company\Event\CompanyArchived;
use App\Company\Event\CompanyInnChanged;
use App\Company\Event\CompanyRemoved;
use App\Company\Event\CompanyRenamed;
use App\Company\Event\CompanyRestored;
use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

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
        #[ORM\Column(name: 'user_id', type: 'company_user_id')]
        private UserId $userId,
        #[ORM\Column(type: 'string', length: 16, enumType: CompanyStatus::class, options: ['default' => CompanyStatus::ACTIVE])]
        private CompanyStatus $status = CompanyStatus::ACTIVE,
    ) {}

    /**
     * Фабричный метод: зарегистрировать нового контрагента за пользователем.
     *
     * Агрегат с момента создания знает своего владельца ($userId),
     * что позволяет в будущем защищать данные от чужих изменений.
     */
    public static function create(
        Id $id,
        Name $name,
        Inn $inn,
        UserId $userId,
    ): self {
        $company = new self($id, $name, $inn, $userId);

        $company->recordEvent(new CompanyAdded($id, $name, $inn, $userId));

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

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getStatus(): CompanyStatus
    {
        return $this->status;
    }

    public function isArchived(): bool
    {
        return CompanyStatus::ARCHIVED === $this->status;
    }

    public function rename(Name $newName): void
    {
        if ($this->name->isEqualTo($newName)) {
            throw new DomainException('Company already has this name.');
        }

        $this->name = $newName;

        $this->recordEvent(new CompanyRenamed($this->id, $newName));
    }

    public function changeInn(Inn $newInn): void
    {
        if ($this->inn->isEqualTo($newInn)) {
            throw new DomainException('Company already has this INN.');
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
        if (CompanyStatus::ARCHIVED === $this->status) {
            throw new DomainException('Company is already archived.');
        }

        $this->status = CompanyStatus::ARCHIVED;

        $this->recordEvent(new CompanyArchived($this->id));
    }

    /**
     * Восстанавливает компанию из архива.
     *
     * Инвариант: нельзя восстановить уже активную компанию.
     */
    public function restore(): void
    {
        if (CompanyStatus::ACTIVE === $this->status) {
            throw new DomainException('Company is already active.');
        }

        $this->status = CompanyStatus::ACTIVE;

        $this->recordEvent(new CompanyRestored($this->id));
    }

    /**
     * Помечает компанию как безвозвратно удалённую.
     *
     * Инвариант: удалить можно только архивированную компанию.
     */
    public function remove(): void
    {
        if (CompanyStatus::ARCHIVED !== $this->status) {
            throw new DomainException('Only archived companies can be permanently removed.');
        }

        $this->recordEvent(new CompanyRemoved($this->id));
    }
}
