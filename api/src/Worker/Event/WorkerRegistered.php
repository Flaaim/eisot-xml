<?php

declare(strict_types=1);

namespace App\Worker\Event;

use App\Worker\Entity\Worker\CompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\WorkerId;
use DateTimeImmutable;

/**
 * Доменное событие: работник зарегистрирован в системе.
 */
final class WorkerRegistered
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly WorkerId $id,
        public readonly CompanyId $companyId,
        public readonly FullName $fullName,
        public readonly Profession $profession,
        public readonly SnilsInfo $snilsInfo,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
