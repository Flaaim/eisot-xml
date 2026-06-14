<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Webmozart\Assert\Assert;

/**
 * Результат проверки знаний: «удовлетворительно» или «неудовлетворительно».
 */
final class Result
{
    private const string SATISFACTORY   = 'удовлетворительно';
    private const string UNSATISFACTORY = 'неудовлетворительно';

    private const array ALLOWED = [
        self::SATISFACTORY,
        self::UNSATISFACTORY,
    ];

    private function __construct(
        private string $value
    ) {
    }

    public static function satisfactory(): self
    {
        return new self(self::SATISFACTORY);
    }

    public static function unsatisfactory(): self
    {
        return new self(self::UNSATISFACTORY);
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::ALLOWED, true)) {
            throw new \DomainException(sprintf('Invalid result: "%s". Allowed: удовлетворительно, неудовлетворительно.', $value));
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isSatisfactory(): bool
    {
        return $this->value === self::SATISFACTORY;
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
