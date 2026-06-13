<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Webmozart\Assert\Assert;

/**
 * Идентификатор владельца компании из контекста Auth.
 *
 * Bounded Context Company не импортирует App\Auth — связь только по UUID-строке.
 * Хранит строку с UUID пользователя из системы авторизации.
 */
final class UserId
{
    public function __construct(
        private readonly string $value
    ) {
        Assert::uuid($value, 'User ID must be a valid UUID, got: %s');
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
