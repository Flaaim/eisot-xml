<?php

declare(strict_types=1);

namespace App\Subscription\Command\ActivateTrialSubscription;

use App\Auth\Entity\User\Id as AuthUserId;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Entity\Subscription\Id;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\Subscription;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
use DomainException;

/**
 * Активация Trial Subscription.
 *
 * Инварианты:
 *  - Trial доступен только один раз (User.trial_used);
 *  - нет другой Active Status подписки;
 *  - период фиксирован — 3 дня.
 */
final readonly class Handler
{
    private const int TRIAL_DURATION_DAYS = 3;

    public function __construct(
        private UserRepository $users,
        private SubscriptionRepository $subscriptions,
        private Flusher $flusher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Command $command): Id
    {
        $user = $this->users->get(new AuthUserId($command->userId));

        if ($user->isTrialUsed()) {
            throw new DomainException('Trial Subscription has already been used.');
        }

        $userId = new UserId($command->userId);

        if ($this->subscriptions->hasActiveByUserId($userId)) {
            throw new DomainException('User already has an active subscription.');
        }

        $subscription = Subscription::activate(
            Id::generate(),
            $userId,
            Plan::TRIAL,
            Period::fromDurationDays(self::TRIAL_DURATION_DAYS),
        );

        $this->subscriptions->add($subscription);
        $user->markTrialAsUsed();
        $this->flusher->flush();

        return $subscription->getId();
    }
}
