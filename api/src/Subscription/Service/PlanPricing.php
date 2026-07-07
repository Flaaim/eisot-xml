<?php

declare(strict_types=1);

namespace App\Subscription\Service;

use App\Subscription\Entity\Payment\Amount;
use App\Subscription\Entity\Subscription\Plan;
use DomainException;

final class PlanPricing
{
    public static function amountFor(Plan $plan): Amount
    {
        return match ($plan) {
            Plan::BASIC => Amount::fromRubles('490.00'),
            Plan::PREMIUM => Amount::fromRubles('2490.00'),
        };
    }

    public static function durationDaysFor(Plan $plan): int
    {
        return match ($plan) {
            Plan::BASIC, Plan::PREMIUM => 30,
        };
    }

    public static function descriptionFor(Plan $plan): string
    {
        return match ($plan) {
            Plan::BASIC => 'User Subscription: Базовый Plan',
            Plan::PREMIUM => 'User Subscription: Премиум Plan',
        };
    }

    public static function resolve(Plan $plan, ?int $durationDays): int
    {
        $defaultDuration = self::durationDaysFor($plan);

        if (null === $durationDays) {
            return $defaultDuration;
        }

        if ($durationDays !== $defaultDuration) {
            throw new DomainException('Unsupported subscription duration for selected plan.');
        }

        return $durationDays;
    }
}
