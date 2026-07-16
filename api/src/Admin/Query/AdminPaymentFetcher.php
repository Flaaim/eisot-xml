<?php

declare(strict_types=1);

namespace App\Admin\Query;

use App\Admin\ReadModel\AdminPaymentFetcherInterface;
use Doctrine\DBAL\Connection;

/**
 * DBAL Read Model: платежи User Subscription для Admin-панели.
 *
 * @psalm-suppress UnusedClass
 */
final readonly class AdminPaymentFetcher implements AdminPaymentFetcherInterface
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function findPayments(int $limit, int $offset): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select(
                'p.id',
                'p.user_id',
                'u.email AS user_email',
                'p.plan',
                'p.status',
                'p.amount_value',
                'p.amount_currency',
                'p.created_at',
                'p.confirmed_at',
            )
            ->from('subscription_payments', 'p')
            ->leftJoin('p', 'users', 'u', 'u.id = p.user_id')
            ->orderBy('p.created_at', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (string)$row['id'],
                'user_id' => (string)$row['user_id'],
                'user_email' => isset($row['user_email']) && null !== $row['user_email']
                    ? (string)$row['user_email']
                    : '',
                'plan' => (string)$row['plan'],
                'status' => (string)$row['status'],
                'amount_value' => (string)$row['amount_value'],
                'amount_currency' => (string)$row['amount_currency'],
                'created_at' => (string)$row['created_at'],
                'confirmed_at' => isset($row['confirmed_at']) && null !== $row['confirmed_at']
                    ? (string)$row['confirmed_at']
                    : null,
            ];
        }

        return $result;
    }

    public function countPayments(): int
    {
        return (int)$this->connection->fetchOne('SELECT COUNT(id) FROM subscription_payments');
    }
}
