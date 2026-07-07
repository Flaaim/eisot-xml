<?php

declare(strict_types=1);

namespace App\Subscription\Command\FailPayment;

final class Command
{
    public function __construct(
        public readonly string $externalId,
    ) {}
}
