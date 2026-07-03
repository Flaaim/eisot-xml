<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DomainException;

final class TrainingRecordRepository
{
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $em)
    {
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(TrainingRecord::class);
        $this->repo = $repo;
        $this->em   = $em;
    }

    /**
     * @throws DomainException
     */
    public function get(Id $id): TrainingRecord
    {
        if (!$record = $this->repo->find($id->getValue())) {
            throw new DomainException('Training record is not found.');
        }
        /** @var TrainingRecord $record */
        return $record;
    }

    public function add(TrainingRecord $record): void
    {
        $this->em->persist($record);
    }
}
