<?php

declare(strict_types=1);

namespace App\Admin\ReadModel;

interface AdminPaymentFetcherInterface
{
    /**
     * @return list<array{
     *     id: string,
     *     user_id: string,
     *     user_email: string,
     *     plan: string,
     *     status: string,
     *     amount_value: string,
     *     amount_currency: string,
     *     created_at: string,
     *     confirmed_at: ?string
     * }>
     */
    public function findPayments(int $limit, int $offset): array;

    public function countPayments(): int;
}
