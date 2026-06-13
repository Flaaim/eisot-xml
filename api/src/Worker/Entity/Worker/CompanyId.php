<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Webmozart\Assert\Assert;

/**
 * Ссылка на компанию внутри контекста Worker.
 *
 * Хранит UUID компании как строку — без импорта App\Company.
 * Bounded Contexts общаются исключительно по идентификатору.
 */
final class CompanyId
{
    public function __construct(
        private string $value
    ) {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
