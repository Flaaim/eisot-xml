<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

/**
 * Тарифный Plan подписки (Ubiquitous Language).
 */
enum Plan: string
{
    case BASIC = 'basic';
    case PREMIUM = 'premium';
}
