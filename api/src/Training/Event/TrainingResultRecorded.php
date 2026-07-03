<?php

declare(strict_types=1);

namespace App\Training\Event;

use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\Program;
use App\Training\Entity\Record\ProtocolNumber;
use App\Training\Entity\Record\Result;
use App\Training\Entity\Record\WorkerId;
use DateTimeImmutable;

/**
 * Доменное событие: результат обучения зафиксирован.
 */
final class TrainingResultRecorded
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
        public readonly WorkerId $workerId,
        public readonly Program $program,
        public readonly Result $result,
        public readonly DateTimeImmutable $date,
        public readonly ProtocolNumber $protocolNumber,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
