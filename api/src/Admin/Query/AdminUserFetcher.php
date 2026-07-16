<?php

declare(strict_types=1);

namespace App\Admin\Query;

use App\Admin\ReadModel\AdminUserFetcherInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * DBAL Read Model: User + Subscription + Company counts для Admin-панели.
 *
 * @psalm-suppress UnusedClass
 */
final readonly class AdminUserFetcher implements AdminUserFetcherInterface
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function findUsers(int $limit, int $offset, ?string $email, ?string $subscriptionStatus): array
    {
        $qb = $this->baseUsersQuery();
        $this->applyFilters($qb, $email, $subscriptionStatus);

        $qb
            ->select(
                'u.id',
                'u.email',
                'u.status',
                'u.role',
                'u.date AS created_at',
                's.plan AS active_subscription_plan',
                's.status AS subscription_status',
                '(SELECT COUNT(c.id) FROM companies c WHERE c.user_id = u.id) AS companies_count',
            )
            ->orderBy('u.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $rows = $qb->executeQuery()->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'id' => (string)$row['id'],
                'email' => (string)$row['email'],
                'status' => (string)$row['status'],
                'role' => (string)$row['role'],
                'created_at' => (string)$row['created_at'],
                'active_subscription_plan' => isset($row['active_subscription_plan']) && null !== $row['active_subscription_plan']
                    ? (string)$row['active_subscription_plan']
                    : null,
                'subscription_status' => isset($row['subscription_status']) && null !== $row['subscription_status']
                    ? (string)$row['subscription_status']
                    : null,
                'companies_count' => (int)$row['companies_count'],
            ];
        }

        return $result;
    }

    public function countUsers(?string $email, ?string $subscriptionStatus): int
    {
        $qb = $this->baseUsersQuery();
        $this->applyFilters($qb, $email, $subscriptionStatus);
        $qb->select('COUNT(DISTINCT u.id)');

        return (int)$qb->executeQuery()->fetchOne();
    }

    public function getSubscriptionStats(): array
    {
        $totalUsers = (int)$this->connection->fetchOne('SELECT COUNT(id) FROM users');

        $registrationsLast30Days = (int)$this->connection->fetchOne(
            "SELECT COUNT(id) FROM users WHERE date >= (CURRENT_DATE - INTERVAL '30 days')",
        );

        $activeSubscriptions = (int)$this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(id) FROM subscriptions
                WHERE status = 'active' AND period_end >= CURRENT_DATE
                SQL,
        );

        $activeBasicPlan = (int)$this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(id) FROM subscriptions
                WHERE status = 'active' AND period_end >= CURRENT_DATE AND plan = 'basic'
                SQL,
        );

        $activeExtendedPlan = (int)$this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(id) FROM subscriptions
                WHERE status = 'active' AND period_end >= CURRENT_DATE AND plan = 'extended'
                SQL,
        );

        $activeSubscriptionsLast30Days = (int)$this->connection->fetchOne(
            <<<'SQL'
                SELECT COUNT(id) FROM subscriptions
                WHERE status = 'active'
                  AND period_end >= CURRENT_DATE
                  AND period_start >= (CURRENT_DATE - INTERVAL '30 days')
                SQL,
        );

        return [
            'total_users' => $totalUsers,
            'registrations_last_30_days' => $registrationsLast30Days,
            'active_subscriptions' => $activeSubscriptions,
            'active_basic_plan' => $activeBasicPlan,
            'active_extended_plan' => $activeExtendedPlan,
            'active_subscriptions_last_30_days' => $activeSubscriptionsLast30Days,
        ];
    }

    private function baseUsersQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from('users', 'u')
            ->leftJoin(
                'u',
                'subscriptions',
                's',
                "s.id = (
                    SELECT s2.id FROM subscriptions s2
                    WHERE s2.user_id = u.id
                      AND s2.status = 'active'
                      AND s2.period_end >= CURRENT_DATE
                    ORDER BY s2.period_end DESC
                    LIMIT 1
                )",
            );
    }

    private function applyFilters(QueryBuilder $qb, ?string $email, ?string $subscriptionStatus): void
    {
        if (null !== $email && '' !== trim($email)) {
            $qb->andWhere('LOWER(u.email) LIKE :email')
                ->setParameter('email', '%' . mb_strtolower(trim($email)) . '%');
        }

        if ('active' === $subscriptionStatus) {
            $qb->andWhere('s.id IS NOT NULL');
        }

        if ('none' === $subscriptionStatus) {
            $qb->andWhere('s.id IS NULL');
        }

        if ('expired' === $subscriptionStatus) {
            $qb->andWhere('s.id IS NULL')
                ->andWhere(
                    <<<'SQL'
                        EXISTS (
                            SELECT 1 FROM subscriptions sx
                            WHERE sx.user_id = u.id
                              AND (sx.status = 'expired' OR sx.period_end < CURRENT_DATE)
                        )
                        SQL
                );
        }
    }
}
