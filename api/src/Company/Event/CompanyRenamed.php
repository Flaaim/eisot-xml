<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Name;
use DateTimeImmutable;

/**
 * Доменное событие: контрагент переименован.
 *
 * Имя отражает бизнес-факт (переименование),
 * а не техническую CRUD-операцию.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class CompanyRenamed
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
        public readonly Name $name,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
