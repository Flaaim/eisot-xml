<?php

declare(strict_types=1);

namespace App\Admin\Query\GetPaymentsList;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class PaymentsListResult
{
    /**
     * @param list<PaymentSummaryDTO> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {}
}
