<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;

/**
 * Доменное событие: компания восстановлена из архива.
 */
final class CompanyRestored
{
    public readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id $id,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
