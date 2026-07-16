<?php

declare(strict_types=1);

namespace App\Subscription\Test\Unit\Entity\Subscription;

use App\Subscription\Entity\Subscription\Id;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\Subscription;
use App\Subscription\Entity\Subscription\SubscriptionStatus;
use App\Subscription\Entity\Subscription\UserId;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class SubscriptionTest extends TestCase
{
    private const string USER_ID = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';

    public function testActivateCreatesActiveUserSubscription(): void
    {
        $period = Period::fromDurationDays(30);
        $subscription = Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::BASIC,
            $period,
        );

        self::assertTrue($subscription->isActive());
        self::assertSame(Plan::BASIC, $subscription->getPlan());
        self::assertSame(SubscriptionStatus::ACTIVE, $subscription->getStatus());
        self::assertCount(1, $subscription->releaseEvents());
    }

    public function testPeriodCannotEndInPast(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Period(
            new DateTimeImmutable('today'),
            new DateTimeImmutable('yesterday'),
        );
    }

    public function testExtendUpdatesPeriodEnd(): void
    {
        $subscription = Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::EXTENDED,
            Period::fromDurationDays(30),
        );

        $originalEnd = $subscription->getPeriod()->getEndDate();
        $subscription->extend(10);

        self::assertTrue($subscription->getPeriod()->getEndDate() > $originalEnd);
    }

    public function testExpireRecordsEvent(): void
    {
        $subscription = Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::BASIC,
            Period::fromDurationDays(30),
        );

        $subscription->expire();

        self::assertFalse($subscription->isActive());
        self::assertSame(SubscriptionStatus::EXPIRED, $subscription->getStatus());
        self::assertCount(2, $subscription->releaseEvents());
    }

    public function testChangePlan(): void
    {
        $subscription = Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::BASIC,
            Period::fromDurationDays(30),
        );

        $subscription->changePlan(Plan::EXTENDED);

        self::assertTrue($subscription->isActive());
        self::assertSame(Plan::EXTENDED, $subscription->getPlan());
    }

    public function testTrialRequiresExactlyThreeDays(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Trial Subscription Period must be exactly 3 days.');

        Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::TRIAL,
            Period::fromDurationDays(30),
        );
    }

    public function testTrialActivatesForThreeDays(): void
    {
        $subscription = Subscription::activate(
            Id::generate(),
            new UserId(self::USER_ID),
            Plan::TRIAL,
            Period::fromDurationDays(3),
        );

        self::assertTrue($subscription->isActive());
        self::assertSame(Plan::TRIAL, $subscription->getPlan());
        self::assertSame(3, $subscription->getPeriod()->getDurationDays());
    }
}
