<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Webmozart\Assert\Assert;

final class Inn
{
    private const LENGTH_10 = 10;
    private const LENGTH_12 = 12;

    public function __construct(
        private string $value
    ) {
        Assert::regex($value, '/^\d{10}(\d{2})?$/', 'INN must consist of 10 or 12 digits.');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isLegal(): bool
    {
        return strlen($this->value) === self::LENGTH_10;
    }

    public function isIndividual(): bool
    {
        return strlen($this->value) === self::LENGTH_12;
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
