<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DomainException;

final class CompanyRepository
{
    private EntityRepository $repo;
    private EntityManagerInterface $em;
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $em)
    {
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(Company::class);
        $this->repo = $repo;
        $this->em   = $em;
    }

    public function hasByInn(Inn $inn): bool
    {
        return $this->repo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.inn = :inn')
            ->setParameter(':inn', $inn->getValue())
            ->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @throws DomainException
     */
    public function get(Id $id): Company
    {
        $company = $this->repo->find($id->getValue());
        if (null === $company) {
            throw new DomainException('Company is not found.');
        }
        /** @var Company $company */
        return $company;
    }

    public function add(Company $company): void
    {
        $this->em->persist($company);
    }

    /**
     * Возвращает только активные (не архивированные) компании текущего пользователя.
     *
     * Query-метод: фильтрует строго по user_id — пользователь видит только свои компании.
     * Используется для query-эндпоинтов (списки, XML-выгрузки ЕИСОТ).
     *
     * @return Company[]
     */
    public function findAllActiveByUser(UserId $userId): array
    {
        return $this->repo->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.status = :status')
            ->setParameter('userId', $userId->getValue())
            ->setParameter('status', CompanyStatus::ACTIVE)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
