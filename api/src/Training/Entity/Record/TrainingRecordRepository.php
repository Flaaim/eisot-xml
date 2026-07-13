<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DomainException;

final class TrainingRecordRepository
{
    private EntityRepository $repo;
    private EntityManagerInterface $em;
    private Connection $connection;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(EntityManagerInterface $em)
    {
        /** @var EntityRepository $repo */
        $repo = $em->getRepository(TrainingRecord::class);
        $this->repo = $repo;
        $this->em   = $em;
        $this->connection = $em->getConnection();
    }

    /**
     * @throws DomainException
     */
    public function get(Id $id): TrainingRecord
    {
        $record = $this->repo->find($id->getValue());
        if (null === $record) {
            throw new DomainException('Training record is not found.');
        }
        /** @var TrainingRecord $record */
        return $record;
    }

    public function find(Id $id): ?TrainingRecord
    {
        /** @var TrainingRecord */
        return $this->repo->find($id->getValue());
    }

    public function add(TrainingRecord $record): void
    {
        $this->em->persist($record);
    }

    public function deleteAllByCompanyId(string $companyId): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
                DELETE FROM training_records
                WHERE worker_id IN (
                    SELECT id FROM workers WHERE company_id = :companyId
                )
                SQL,
            ['companyId' => $companyId],
        );
    }

    public function removeRecord(TrainingRecord $record): void
    {
        $this->em->remove($record);
    }
}
