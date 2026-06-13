<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use App\Worker\Event\WorkerRegistered;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'workers')]
final class Worker implements AggregateRoot
{
    use EventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'worker_id')]
        private WorkerId $id,
        #[ORM\Column(name: 'company_id', type: 'worker_company_id')]
        private CompanyId $companyId,
        #[ORM\Column(type: 'worker_full_name')]
        private FullName $fullName,
        #[ORM\Column(type: 'worker_profession')]
        private Profession $profession,
        #[ORM\Column(name: 'snils_info', type: 'worker_snils_info')]
        private SnilsInfo $snilsInfo,
    ) {
    }

    /**
     * Фабричный метод: зарегистрировать нового работника за компанией.
     */
    public static function register(
        WorkerId   $id,
        CompanyId  $companyId,
        FullName   $fullName,
        Profession $profession,
        SnilsInfo  $snilsInfo,
    ): self {
        $worker = new self($id, $companyId, $fullName, $profession, $snilsInfo);

        $worker->recordEvent(new WorkerRegistered($id, $companyId, $fullName, $profession, $snilsInfo));

        return $worker;
    }

    public function getId(): WorkerId
    {
        return $this->id;
    }

    public function getCompanyId(): CompanyId
    {
        return $this->companyId;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public function getProfession(): Profession
    {
        return $this->profession;
    }

    public function getSnilsInfo(): SnilsInfo
    {
        return $this->snilsInfo;
    }
}
