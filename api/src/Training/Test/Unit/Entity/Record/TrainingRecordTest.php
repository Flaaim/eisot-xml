<?php

declare(strict_types=1);

namespace App\Training\Test\Unit\Entity\Record;

use App\Training\Entity\Record\RegistryNumber;
use App\Training\Event\RegistryNumberAttached;
use App\Training\Event\TrainingResultRecorded;
use App\Training\Test\Builder\TrainingRecordBuilder;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class TrainingRecordTest extends TestCase
{
    public function testRecord(): void
    {
        $record = (new TrainingRecordBuilder())->build();

        self::assertNotNull($record->getId());
        self::assertNotNull($record->getWorkerId());
        self::assertNotNull($record->getProgram());
        self::assertNotNull($record->getResult());
        self::assertNotNull($record->getDate());
        self::assertNotNull($record->getProtocolNumber());
        self::assertNull($record->getRegistryNumber());
    }

    public function testRecordRecordsTrainingResultRecordedEvent(): void
    {
        $record = (new TrainingRecordBuilder())->build();

        $events = $record->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(TrainingResultRecorded::class, $events[0]);
    }

    public function testAttachRegistryNumber(): void
    {
        $record = (new TrainingRecordBuilder())->build();
        $record->releaseEvents(); // clear creation event

        $registryNumber = RegistryNumber::fromString('РЕГ-001/2023');
        $record->attachRegistryNumber($registryNumber);

        self::assertNotNull($record->getRegistryNumber());
        self::assertEquals('РЕГ-001/2023', $record->getRegistryNumber()->getValue());

        $events = $record->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(RegistryNumberAttached::class, $events[0]);
    }

    public function testAttachRegistryNumberTwiceThrowsException(): void
    {
        $record = (new TrainingRecordBuilder())->build();

        $record->attachRegistryNumber(RegistryNumber::fromString('РЕГ-001'));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Registry number is already attached.');

        $record->attachRegistryNumber(RegistryNumber::fromString('РЕГ-002'));
    }

    public function testEventContainsCorrectData(): void
    {
        $record = (new TrainingRecordBuilder())->build();

        $events = $record->releaseEvents();
        /** @var TrainingResultRecorded $event */
        $event = $events[0];

        self::assertSame($record->getId()->getValue(), $event->id->getValue());
        self::assertSame($record->getWorkerId()->getValue(), $event->workerId->getValue());
        self::assertSame($record->getProgram()->getValue(), $event->program->getValue());
        self::assertSame($record->getResult()->getValue(), $event->result->getValue());
    }
}
