<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use App\Subscription\Event\SubscriptionExpired;
use App\Subscription\Event\SubscriptionPurchased;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
/** @psalm-suppress PossiblyUnusedMethod */
#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
final class Subscription implements AggregateRoot
{
    use EventTrait;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'subscription_id')]
        private Id $id,
        #[ORM\Column(name: 'user_id', type: 'subscription_user_id')]
        private UserId $userId,
        #[ORM\Column(type: 'string', length: 16, enumType: Plan::class)]
        private Plan $plan,
        #[ORM\Column(type: 'string', length: 16, enumType: SubscriptionStatus::class, options: ['default' => SubscriptionStatus::ACTIVE])]
        private SubscriptionStatus $status,
        #[ORM\Column(name: 'period_start', type: 'date_immutable')]
        private DateTimeImmutable $periodStart,
        #[ORM\Column(name: 'period_end', type: 'date_immutable')]
        private DateTimeImmutable $periodEnd,
    ) {}

    /**
     * Активирует User Subscription.
     *
     * Инвариант «дата окончания не в прошлом» проверяется в Period.
     */
    public static function activate(
        Id $id,
        UserId $userId,
        Plan $plan,
        Period $period,
    ): self {
        $subscription = new self(
            $id,
            $userId,
            $plan,
            SubscriptionStatus::ACTIVE,
            $period->getStartDate(),
            $period->getEndDate(),
        );

        $subscription->recordEvent(new SubscriptionPurchased(
            $id,
            $userId,
            $plan,
            $period,
        ));

        return $subscription;
    }

    public function getId(): Id
    {
        return $this->id;
    }
    /** @psalm-suppress PossiblyUnusedMethod */
    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function getPeriod(): Period
    {
        return new Period($this->periodStart, $this->periodEnd);
    }

    /**
     * Проверяет Active Status с учётом Subscription Period.
     */
    public function isActive(): bool
    {
        if (SubscriptionStatus::ACTIVE !== $this->status) {
            return false;
        }

        return $this->getPeriod()->isActiveAt(new DateTimeImmutable('today'));
    }

    /**
     * Продлевает Subscription Period (Supple Design).
     */
    public function extend(int $additionalDays): void
    {
        if (SubscriptionStatus::CANCELLED === $this->status) {
            throw new DomainException('Cancelled User Subscription cannot be extended.');
        }

        $extendedPeriod = $this->getPeriod()->extend($additionalDays);
        $this->periodEnd = $extendedPeriod->getEndDate();

        if (SubscriptionStatus::EXPIRED === $this->status && $this->isActive()) {
            $this->status = SubscriptionStatus::ACTIVE;
        }
    }

    /**
     * Переводит подписку в статус expired и фиксирует событие.
     */
    public function expire(): void
    {
        if (SubscriptionStatus::EXPIRED === $this->status) {
            return;
        }

        if (SubscriptionStatus::CANCELLED === $this->status) {
            throw new DomainException('Cancelled User Subscription cannot expire.');
        }

        $this->status = SubscriptionStatus::EXPIRED;

        $this->recordEvent(new SubscriptionExpired($this->id, $this->userId));
    }


    public function cancel(): void
    {
        if (SubscriptionStatus::CANCELLED === $this->status) {
            throw new DomainException('User Subscription is already cancelled.');
        }

        $this->status = SubscriptionStatus::CANCELLED;
    }
}
