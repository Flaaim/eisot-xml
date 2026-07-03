<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Webmozart\Assert\Assert;

/**
 * Ссылка на работника внутри контекста Training.
 *
 * Хранит UUID работника как строку — без импорта App\Worker.
 */
final class WorkerId
{
    public function __construct(
        private string $value
    ) {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
