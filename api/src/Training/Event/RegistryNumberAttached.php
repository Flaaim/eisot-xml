<?php

declare(strict_types=1);

namespace App\Training\Event;

use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\RegistryNumber;
use DateTimeImmutable;

/**
 * Доменное событие: регистрационный номер прикреплён к записи об обучении.
 */
final class RegistryNumberAttached
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
        public readonly RegistryNumber $registryNumber,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
