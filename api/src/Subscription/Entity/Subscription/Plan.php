<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

/**
 * Тарифный Plan подписки (Ubiquitous Language).
 */
enum Plan: string
{
    case BASIC = 'basic';
    case EXTENDED = 'extended';
    case TRIAL = 'trial';

    public function canAddMoreCompanies(int $currentCount): bool
    {
        return match ($this) {
            self::BASIC, self::TRIAL => $currentCount < 1,
            self::EXTENDED => true,
        };
    }

    public function isTrial(): bool
    {
        return self::TRIAL === $this;
    }
}
