<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

/**
 * Active Status подписки.
 */
enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
