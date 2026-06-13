<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;

/**
 * Доменное событие: ИНН контрагента изменён.
 *
 * Имя отражает бизнес-факт (смена ИНН),
 * а не техническую CRUD-операцию.
 */
final class CompanyInnChanged
{
    public readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id  $id,
        public readonly Inn $inn,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
