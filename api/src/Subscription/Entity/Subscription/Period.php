<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

use InvalidArgumentException;

/**
 * Subscription Period — период действия подписки.
 */
final class Period
{
    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
    ) {
        $startDate = $startDate->setTime(0, 0);
        $endDate = $endDate->setTime(0, 0);

        if ($endDate < $startDate) {
            throw new InvalidArgumentException('Subscription Period end date must be on or after start date.');
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function fromDurationDays(int $durationDays, ?\DateTimeImmutable $startDate = null): self
    {
        if ($durationDays < 1) {
            throw new InvalidArgumentException('Subscription Period duration must be at least one day.');
        }

        $start = ($startDate ?? new \DateTimeImmutable('today'))->setTime(0, 0);
        $end = $start->modify(sprintf('+%d days', $durationDays));

        self::assertEndDateNotInPast($end);

        return new self($start, $end);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isActiveAt(\DateTimeImmutable $moment): bool
    {
        $day = $moment->setTime(0, 0);

        return $day >= $this->startDate && $day <= $this->endDate;
    }

    public function extend(int $additionalDays): self
    {
        if ($additionalDays < 1) {
            throw new InvalidArgumentException('Subscription Period extension must be at least one day.');
        }

        return new self(
            $this->startDate,
            $this->endDate->modify(sprintf('+%d days', $additionalDays)),
        );
    }

    private static function assertEndDateNotInPast(\DateTimeImmutable $endDate): void
    {
        $today = new \DateTimeImmutable('today');

        if ($endDate < $today) {
            throw new InvalidArgumentException('Subscription Period cannot end in the past.');
        }
    }
}
