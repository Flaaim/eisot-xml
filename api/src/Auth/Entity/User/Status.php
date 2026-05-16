<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

final class Status
{
    private const WAIT = 'wait';
    private const ACTIVE = 'active';

    private function __construct(
        private readonly string $name
    ) {}

    public static function wait(): self
    {
        return new self(self::WAIT);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public function isWait(): bool
    {
        return self::WAIT === $this->name;
    }

    public function isActive(): bool
    {
        return self::ACTIVE === $this->name;
    }
}
