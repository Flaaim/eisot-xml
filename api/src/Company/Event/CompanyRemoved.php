<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;
use DateTimeImmutable;

/**
 * Доменное событие: компания безвозвратно удалена из системы.
 *
 * @psalm-suppress PossiblyUnusedProperty
 */
final class CompanyRemoved
{
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }
}
