<?php

declare(strict_types=1);

namespace App\Admin\Query\GetPaymentsList;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class PaymentSummaryDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $userEmail,
        public string $plan,
        public string $status,
        public string $amountValue,
        public string $amountCurrency,
        public string $createdAt,
        public ?string $confirmedAt,
    ) {}
}
