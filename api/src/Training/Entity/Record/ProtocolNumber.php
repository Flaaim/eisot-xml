<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Webmozart\Assert\Assert;

/**
 * Номер протокола проверки знаний.
 */
final class ProtocolNumber
{
    public function __construct(
        private string $value
    ) {
        Assert::notEmpty($value, 'Protocol number must not be empty.');
        Assert::maxLength($value, 100, 'Protocol number must not exceed 100 characters.');
    }

    public function __toString(): string
    {
        return $this->value;
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
}
