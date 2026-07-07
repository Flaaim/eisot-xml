<?php

declare(strict_types=1);

namespace App\Subscription\Query\GetPaymentStatus;

final readonly class PaymentStatusDTO
{
    public function __construct(
        public string $paymentId,
        public string $status,
        public string $planId,
    ) {}
}
