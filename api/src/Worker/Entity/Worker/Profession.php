<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Webmozart\Assert\Assert;

/**
 * Профессия / должность работника согласно штатному расписанию.
 */
final class Profession
{
    public function __construct(
        private string $value
    ) {
        Assert::notEmpty($value, 'Profession must not be empty.');
        Assert::maxLength($value, 200, 'Profession must not exceed 200 characters.');
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
    /** @psalm-suppress PossiblyUnusedMethod */
    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }
}
