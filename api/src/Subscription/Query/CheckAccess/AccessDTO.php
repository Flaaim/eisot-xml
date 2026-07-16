<?php

declare(strict_types=1);

namespace App\Subscription\Query\CheckAccess;

final readonly class AccessDTO
{
    public function __construct(
        public bool $hasAccess,
        public ?string $plan,
        public ?string $status,
        public ?string $periodStart,
        public ?string $periodEnd,
        public bool $trialUsed,
        public bool $trialAvailable,
    ) {}
}
