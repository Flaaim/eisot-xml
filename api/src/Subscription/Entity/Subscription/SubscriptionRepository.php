<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DomainException;

final class SubscriptionRepository
{
    /** @var EntityRepository<Subscription> */
    private EntityRepository $repository;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        $this->repository = $em->getRepository(Subscription::class);
    }

    public function add(Subscription $subscription): void
    {
        $this->em->persist($subscription);
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function get(Id $id): Subscription
    {
        $subscription = $this->repository->find($id->getValue());

        if (!$subscription instanceof Subscription) {
            throw new DomainException('Subscription is not found.');
        }

        return $subscription;
    }

    public function findActiveByUserId(UserId $userId): ?Subscription
    {
        /** @var Subscription[] $subscriptions */
        $subscriptions = $this->repository->findBy(
            ['userId' => $userId, 'status' => SubscriptionStatus::ACTIVE],
            ['periodEnd' => 'DESC'],
        );

        foreach ($subscriptions as $subscription) {
            if ($subscription->isActive()) {
                return $subscription;
            }

            $subscription->expire();
        }

        return null;
    }

    public function hasActiveByUserId(UserId $userId): bool
    {
        return $this->findActiveByUserId($userId) instanceof Subscription;
    }
}
