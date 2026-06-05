<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Webmozart\Assert\Assert;

final class Name
{
    public function __construct(
        private string $value
    ) {
        Assert::notEmpty($value, 'Company name cannot be empty.');
        Assert::maxLength($value, 500, 'Company name cannot be longer than 500 characters.');
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
