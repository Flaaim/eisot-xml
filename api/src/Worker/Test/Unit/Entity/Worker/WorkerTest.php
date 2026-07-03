<?php

declare(strict_types=1);

namespace App\Worker\Test\Unit\Entity\Worker;

use App\Worker\Entity\Worker\CompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\WorkerId;
use App\Worker\Event\WorkerRegistered;
use App\Worker\Test\Builder\WorkerBuilder;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
final class WorkerTest extends TestCase
{
    public function testRegister(): void
    {
        $worker = (new WorkerBuilder())->build();

        self::assertNotNull($worker->getId());
        self::assertNotNull($worker->getCompanyId());
        self::assertNotNull($worker->getFullName());
        self::assertNotNull($worker->getProfession());
        self::assertNotNull($worker->getSnilsInfo());
    }

    public function testRegisterStoresCorrectValues(): void
    {
        $id         = WorkerId::generate();
        $companyId  = new CompanyId(Uuid::uuid4()->toString());
        $fullName   = FullName::create('Петров', 'Пётр', 'Петрович');
        $profession = Profession::fromString('Электрик');
        $snilsInfo  = SnilsInfo::forCitizen(Snils::fromString('112-233-445 95'));

        $worker = (new WorkerBuilder())
            ->withId($id)
            ->withCompanyId($companyId)
            ->withFullName($fullName)
            ->withProfession($profession)
            ->withSnilsInfo($snilsInfo)
            ->build();

        self::assertSame($id->getValue(), $worker->getId()->getValue());
        self::assertSame($companyId->getValue(), $worker->getCompanyId()->getValue());
        self::assertTrue($fullName->isEqualTo($worker->getFullName()));
        self::assertSame($profession->getValue(), $worker->getProfession()->getValue());
        self::assertFalse($worker->getSnilsInfo()->isForeigner());
    }

    public function testRegisterRecordsWorkerRegisteredEvent(): void
    {
        $worker = (new WorkerBuilder())->build();

        $events = $worker->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(WorkerRegistered::class, $events[0]);
    }

    public function testRegisterWithForeignerSnilsInfo(): void
    {
        $snilsInfo = SnilsInfo::forForeigner('Узбекистан', 'UZ-12345');

        $worker = (new WorkerBuilder())
            ->withSnilsInfo($snilsInfo)
            ->build();

        self::assertTrue($worker->getSnilsInfo()->isForeigner());
        self::assertEquals('Узбекистан', $worker->getSnilsInfo()->getCitizenship());
        self::assertEquals('UZ-12345', $worker->getSnilsInfo()->getForeignSnils());
        self::assertNull($worker->getSnilsInfo()->getSnils());
    }

    public function testRegisterEventContainsCorrectData(): void
    {
        $companyId = new CompanyId(Uuid::uuid4()->toString());
        $fullName  = FullName::create('Сидоров', 'Сидор');

        $worker = (new WorkerBuilder())
            ->withCompanyId($companyId)
            ->withFullName($fullName)
            ->build();

        $events = $worker->releaseEvents();
        /** @var WorkerRegistered $event */
        $event = $events[0];

        self::assertSame($worker->getId()->getValue(), $event->id->getValue());
        self::assertSame($companyId->getValue(), $event->companyId->getValue());
        self::assertTrue($fullName->isEqualTo($event->fullName));
    }
}
