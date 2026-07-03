<?php

declare(strict_types=1);

namespace App\Subscription\Service;

use App\Subscription\Exception\SubscriptionRequiredException;
use App\Subscription\Query\CheckAccess\Handler as CheckAccessHandler;
use App\Subscription\Query\CheckAccess\Query;

/**
 * Блокировка формирования RegistrySet XML без Active Status User Subscription.
 */
final readonly class SubscriptionAccessGuard
{
    public function __construct(
        private CheckAccessHandler $checkAccessHandler,
    ) {}

    public function assertUserHasAccess(string $userId): void
    {
        $access = $this->checkAccessHandler->handle(new Query($userId));

        if (!$access->hasAccess) {
            throw new SubscriptionRequiredException();
        }
    }
}
