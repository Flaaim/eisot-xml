<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

use Webmozart\Assert\Assert;

/**
 * Ссылка на пользователя внутри контекста Subscription (User Subscription).
 */
final class UserId
{
    public function __construct(
        private string $value,
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

    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }
}
