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

    public function canAddMoreCompanies(int $currentCount): bool
    {
        return match ($this) {
            self::BASIC => $currentCount < 1,
            self::EXTENDED => true,
        };
    }
}
