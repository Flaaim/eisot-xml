<?php

declare(strict_types=1);

namespace App\Training\Query\GetRegistryRecords;

use App\Training\Entity\Record\Program;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

/**
 * Обработчик запроса GetRegistryRecords.
 *
 * Выполняет прямой SQL-запрос к БД через DBAL (CQRS Read-Model) без поднятия ORM-сущностей.
 */
final readonly class Handler
{
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @return list<RegistryRecordDTO>
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function handle(Query $query): array
    {
        $qb = $this->connection->createQueryBuilder();

        $rows = $qb
            ->select(
                't.id',
                'w.full_name',
                'w.snils_info',
                'w.profession',
                't.program',
                't.result',
                't.date',
                't.protocol_number'
            )
            ->from('training_records', 't')
            ->innerJoin('t', 'workers', 'w', 't.worker_id = w.id')
            ->innerJoin('w', 'companies', 'c', 'w.company_id = c.id')
            ->where('w.company_id = :companyId')
            ->andWhere('c.user_id = :userId')
            ->setParameter('companyId', $query->companyId)
            ->setParameter('userId', $query->userId)
            ->orderBy('t.date', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            static function (array $row): RegistryRecordDTO {
                $fullName = json_decode((string)($row['full_name'] ?? '{}'), true) ?? [];
                $workerFullName = trim(\sprintf(
                    '%s %s %s',
                    $fullName['last'] ?? '',
                    $fullName['first'] ?? '',
                    $fullName['middle'] ?? ''
                ));

                $snilsInfo = json_decode((string)($row['snils_info'] ?? '{}'), true) ?? [];
                $snils = $snilsInfo['snils'] ?? '';

                $programId = (int)$row['program'];
                $programTitle = Program::catalog()[$programId] ?? 'Неизвестная программа';

                $date = new DateTimeImmutable((string)$row['date']);

                return new RegistryRecordDTO(
                    id: (string)$row['id'],
                    workerFullName: $workerFullName,
                    snils: $snils,
                    profession: (string)($row['profession'] ?? ''),
                    programTitle: $programTitle,
                    result: (string)($row['result'] ?? ''),
                    date: $date->format('Y-m-d'),
                    protocolNumber: (string)($row['protocol_number'] ?? '')
                );
            },
            $rows
        );
    }
}
