<?php

declare(strict_types=1);

namespace App\Admin\Query\GetPaymentsList;

use App\Admin\ReadModel\AdminPaymentFetcherInterface;

final readonly class Handler
{
    public function __construct(
        private AdminPaymentFetcherInterface $fetcher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Query $query): PaymentsListResult
    {
        $offset = ($query->page - 1) * $query->limit;

        $rows = $this->fetcher->findPayments($query->limit, $offset);

        $items = array_map(
            static fn (array $row): PaymentSummaryDTO => new PaymentSummaryDTO(
                id: $row['id'],
                userId: $row['user_id'],
                userEmail: $row['user_email'],
                plan: $row['plan'],
                status: $row['status'],
                amountValue: $row['amount_value'],
                amountCurrency: $row['amount_currency'],
                createdAt: $row['created_at'],
                confirmedAt: $row['confirmed_at'],
            ),
            $rows,
        );

        return new PaymentsListResult(
            items: $items,
            total: $this->fetcher->countPayments(),
            page: $query->page,
            limit: $query->limit,
        );
    }
}
