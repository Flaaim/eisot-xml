<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Webmozart\Assert\Assert;

/**
 * СНИЛС гражданина РФ.
 *
 * Формат: «XXX-XXX-XXX XX» (11 цифр, 3 дефиса, 1 пробел).
 * Пример: «644-670-185 07».
 */
final class Snils
{
    private const string PATTERN = '/^\d{3}-\d{3}-\d{3} \d{2}$/';

    public function __construct(
        private string $value
    ) {
        Assert::regex(
            $value,
            self::PATTERN,
            'SNILS must match format XXX-XXX-XXX XX (e.g. 644-670-185 07).'
        );
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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
