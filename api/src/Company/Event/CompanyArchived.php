<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;
use DateTimeImmutable;

/**
 * Доменное событие: компания переведена в архив.
 *
 * Использует бизнес-семантику (Archived), а не CRUD-семантику (Deleted).
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class CompanyArchived
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
