<?php

declare(strict_types=1);

namespace App\Subscription\Command\CreatePayment;

final readonly class Result
{
    public function __construct(
        public string $paymentId,
        public string $confirmationUrl,
    ) {}
}
