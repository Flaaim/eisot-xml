<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DomainException;

final class WorkerRepository
{
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $em)
    {
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(Worker::class);
        $this->repo = $repo;
        $this->em   = $em;
    }

    /**
     * @throws DomainException
     */
    public function get(WorkerId $id): Worker
    {
        if (!$worker = $this->repo->find($id->getValue())) {
            throw new DomainException('Worker is not found.');
        }
        /** @var Worker $worker */
        return $worker;
    }

    public function add(Worker $worker): void
    {
        $this->em->persist($worker);
    }
}
