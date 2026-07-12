<?php

declare(strict_types=1);

namespace App\Subscription\Service;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Exception\CompanyLimitReachedException;
use App\Subscription\Exception\SubscriptionRequiredException;
use App\Subscription\Query\CheckAccess\Handler as CheckAccessHandler;
use App\Subscription\Query\CheckAccess\Query;

/**
 * Проверка прав User Subscription: доступ к RegistrySet XML и лимиты компаний.
 */
final readonly class SubscriptionAccessGuard
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private CheckAccessHandler $checkAccessHandler,
        private SubscriptionRepository $subscriptions,
        private CompanyRepository $companies,
    ) {}

    public function assertUserHasAccess(string $userId): void
    {
        $access = $this->checkAccessHandler->handle(new Query($userId));

        if (!$access->hasAccess) {
            throw new SubscriptionRequiredException();
        }
    }

    public function assertCanAddCompany(UserId $userId): void
    {
        $subscription = $this->subscriptions->findActiveByUserId($userId);

        $currentCount = $this->companies->countAllByUser(
            new CompanyUserId($userId->getValue()),
        );

        $plan = null !== $subscription ? $subscription->getPlan() : Plan::BASIC;

        if (!$plan->canAddMoreCompanies($currentCount)) {
            throw new CompanyLimitReachedException();
        }
    }

    public function assertCanRestoreCompany(UserId $userId): void
    {
        $subscription = $this->subscriptions->findActiveByUserId($userId);

        $activeCount = $this->companies->countActiveByUser(
            new CompanyUserId($userId->getValue()),
        );

        $plan = null !== $subscription ? $subscription->getPlan() : Plan::BASIC;

        if (!$plan->canAddMoreCompanies($activeCount)) {
            throw new CompanyLimitReachedException();
        }
    }
}
