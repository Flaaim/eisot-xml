<?php

declare(strict_types=1);

namespace App\Company\Entity\Company\Event;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;

/**
 * Доменное событие: контрагент добавлен в систему.
 *
 * Имя отражает бизнес-факт (добавление контрагента),
 * а не техническую CRUD-операцию.
 */
final class CompanyAdded
{
    public readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id   $id,
        public readonly Name $name,
        public readonly Inn  $inn,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
