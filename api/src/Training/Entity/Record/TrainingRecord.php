<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use App\Training\Event\RegistryNumberAttached;
use App\Training\Event\TrainingResultRecorded;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'training_records')]
final class TrainingRecord implements AggregateRoot
{
    use EventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'training_record_id')]
        private Id $id,
        #[ORM\Column(name: 'worker_id', type: 'training_worker_id')]
        private WorkerId $workerId,
        #[ORM\Column(type: 'training_program')]
        private Program $program,
        #[ORM\Column(type: 'training_result')]
        private Result $result,
        #[ORM\Column(type: 'datetime_immutable')]
        private DateTimeImmutable $date,
        #[ORM\Column(name: 'protocol_number', type: 'training_protocol_number')]
        private ProtocolNumber $protocolNumber,
        #[ORM\Column(name: 'registry_number', type: 'training_registry_number', nullable: true)]
        private ?RegistryNumber $registryNumber = null,
    ) {}

    /**
     * Фабричный метод: зафиксировать результат обучения.
     */
    public static function record(
        Id $id,
        WorkerId $workerId,
        Program $program,
        Result $result,
        DateTimeImmutable $date,
        ProtocolNumber $protocolNumber,
    ): self {
        $record = new self($id, $workerId, $program, $result, $date, $protocolNumber);

        $record->recordEvent(new TrainingResultRecorded(
            $id,
            $workerId,
            $program,
            $result,
            $date,
            $protocolNumber,
        ));

        return $record;
    }

    /**
     * Прикрепить регистрационный номер из реестра Минтруда (ЕИСОТ).
     *
     * Инвариант: нельзя прикрепить номер, если он уже прикреплён.
     */
    public function attachRegistryNumber(RegistryNumber $registryNumber): void
    {
        if (null !== $this->registryNumber) {
            throw new DomainException('Registry number is already attached.');
        }

        $this->registryNumber = $registryNumber;

        $this->recordEvent(new RegistryNumberAttached($this->id, $registryNumber));
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getWorkerId(): WorkerId
    {
        return $this->workerId;
    }

    public function getProgram(): Program
    {
        return $this->program;
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getProtocolNumber(): ProtocolNumber
    {
        return $this->protocolNumber;
    }

    public function getRegistryNumber(): ?RegistryNumber
    {
        return $this->registryNumber;
    }
}
