<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Webmozart\Assert\Assert;

/**
 * Регистрационный номер записи о проверке знаний в реестре обученных лиц (ЕИСОТ).
 *
 * Присваивается Минтрудом после загрузки XML в ЕИСОТ.
 * Nullable при первичном формировании, обязателен для повторного импорта.
 */
final class RegistryNumber
{
    public function __construct(
        private string $value
    ) {
        Assert::notEmpty($value, 'Registry number must not be empty.');
        Assert::maxLength($value, 100, 'Registry number must not exceed 100 characters.');
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
