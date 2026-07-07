<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Payment;

use App\Subscription\Entity\Subscription\UserId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class PaymentRepository
{
    private EntityRepository $repo;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        $this->repo = $this->em->getRepository(Payment::class);
    }

    public function add(Payment $payment): void
    {
        $this->em->persist($payment);
    }

    public function findByIdAndUserId(Id $id, UserId $userId): ?Payment
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->where('p.id = :id')
            ->andWhere('p.userId = :userId')
            ->setParameter('id', $id->getValue())
            ->setParameter('userId', $userId->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByExternalId(ExternalId $externalId): ?Payment
    {
        return $this->repo->findOneBy([
            'externalId' => $externalId->getValue(),
        ]);
    }
}
