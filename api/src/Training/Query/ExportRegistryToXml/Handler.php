<?php

declare(strict_types=1);

namespace App\Training\Query\ExportRegistryToXml;

use App\Subscription\Service\SubscriptionAccessGuard;
use App\Training\Entity\Record\Program;
use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use DomainException;
use DOMDocument;

/**
 * Обработчик запроса ExportRegistryToXml.
 *
 * Генерирует XML-документ для Минтруда по выбранным ID записей обучения.
 */
final readonly class Handler
{
    public function __construct(
        private Connection $connection,
        private SubscriptionAccessGuard $subscriptionAccessGuard,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Query $query): string
    {
        if (empty($query->recordIds)) {
            throw new DomainException('No records selected for export.');
        }

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
                't.protocol_number',
                'c.name AS company_name',
                'c.inn AS company_inn'
            )
            ->from('training_records', 't')
            ->innerJoin('t', 'workers', 'w', 't.worker_id = w.id')
            ->innerJoin('w', 'companies', 'c', 'w.company_id = c.id')
            ->where($qb->expr()->in('t.id', ':recordIds'))
            ->andWhere('c.user_id = :userId')
            ->setParameter('recordIds', $query->recordIds, ArrayParameterType::STRING)
            ->setParameter('userId', $query->userId)
            ->orderBy('t.date', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        if (empty($rows)) {
            throw new DomainException('No matching records found.');
        }

        $this->subscriptionAccessGuard->assertUserHasAccess($query->userId);

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $registrySet = $dom->createElement('RegistrySet');
        $registrySet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $registrySet->setAttribute('xsi:noNamespaceSchemaLocation', 'schema.xsd');
        $dom->appendChild($registrySet);

        foreach ($rows as $row) {
            $recordNode = $dom->createElement('RegistryRecord');
            $recordNode->setAttribute('outerId', (string)$row['id']);

            $fullName = json_decode((string)($row['full_name'] ?? '{}'), true) ?? [];
            $snilsInfo = json_decode((string)($row['snils_info'] ?? '{}'), true) ?? [];

            // 1. Worker
            $workerNode = $dom->createElement('Worker');
            $workerNode->appendChild($dom->createElement('LastName', (string)($fullName['last'] ?? '')));
            $workerNode->appendChild($dom->createElement('FirstName', (string)($fullName['first'] ?? '')));
            $workerNode->appendChild($dom->createElement('MiddleName', (string)($fullName['middle'] ?? '')));

            $isForeigner = (bool)($snilsInfo['isForeigner'] ?? false);
            if (!$isForeigner) {
                $workerNode->appendChild($dom->createElement('Snils', (string)($snilsInfo['snils'] ?? '')));
                $workerNode->appendChild($dom->createElement('IsForeignSnils', '0'));
            } else {
                $workerNode->appendChild($dom->createElement('IsForeignSnils', '1'));

                $foreignSnils = (string)($snilsInfo['foreignSnils'] ?? '');
                if ('' !== $foreignSnils) {
                    $workerNode->appendChild($dom->createElement('ForeignSnils', $foreignSnils));
                }

                $citizenship = (string)($snilsInfo['citizenship'] ?? '');
                if ('' !== $citizenship) {
                    $workerNode->appendChild($dom->createElement('Citizenship', $citizenship));
                }
            }

            $workerNode->appendChild($dom->createElement('Position', (string)($row['profession'] ?? '')));
            $workerNode->appendChild($dom->createElement('EmployerInn', (string)($row['company_inn'] ?? '')));
            $workerNode->appendChild($dom->createElement('EmployerTitle', (string)($row['company_name'] ?? '')));

            $recordNode->appendChild($workerNode);

            // 2. Organization
            $orgNode = $dom->createElement('Organization');
            $orgNode->appendChild($dom->createElement('Inn', (string)($row['company_inn'] ?? '')));
            $orgNode->appendChild($dom->createElement('Title', (string)($row['company_name'] ?? '')));
            $recordNode->appendChild($orgNode);

            // 3. Test
            $testNode = $dom->createElement('Test');

            $dateTime = new DateTimeImmutable((string)$row['date']);
            $testNode->appendChild($dom->createElement('Date', $dateTime->format('Y-m-d')));
            $testNode->appendChild($dom->createElement('ProtocolNumber', (string)($row['protocol_number'] ?? '')));

            $programId = (int)$row['program'];
            $programTitle = Program::catalog()[$programId] ?? 'Неизвестная программа';
            $testNode->appendChild($dom->createElement('LearnProgramTitle', $programTitle));

            $isPassed = ('удовлетворительно' === $row['result']) ? '1' : '0';
            $testNode->setAttribute('isPassed', $isPassed);
            $testNode->setAttribute('learnProgramId', (string)$programId);

            $recordNode->appendChild($testNode);

            $registrySet->appendChild($recordNode);
        }

        return $dom->saveXML();
    }
}
