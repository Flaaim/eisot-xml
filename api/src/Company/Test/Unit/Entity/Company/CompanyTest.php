<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Entity\Company;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Entity\Company\UserId;
use App\Company\Event\CompanyAdded;
use App\Company\Event\CompanyArchived;
use App\Company\Event\CompanyInnChanged;
use App\Company\Event\CompanyRenamed;
use App\Company\Test\Builder\CompanyBuilder;
use DomainException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
final class CompanyTest extends TestCase
{
    public function testCreate(): void
    {
        $company = (new CompanyBuilder())->build();

        self::assertInstanceOf(Id::class, $company->getId());
        self::assertInstanceOf(Name::class, $company->getName());
        self::assertInstanceOf(Inn::class, $company->getInn());
        self::assertInstanceOf(UserId::class, $company->getUserId());
    }

    public function testCreateStoresCorrectValues(): void
    {
        $id     = Id::generate();
        $name   = Name::fromString('ООО Тест');
        $inn    = Inn::fromString('7707083893');
        $userId = new UserId(Uuid::uuid4()->toString());

        $company = (new CompanyBuilder())
            ->withId($id)
            ->withName($name)
            ->withInn($inn)
            ->withUserId($userId)
            ->build();

        self::assertSame($id->getValue(), $company->getId()->getValue());
        self::assertSame($name->getValue(), $company->getName()->getValue());
        self::assertSame($inn->getValue(), $company->getInn()->getValue());
        self::assertSame($userId->getValue(), $company->getUserId()->getValue());
    }

    public function testCreateRecordsCompanyAddedEventWithUserId(): void
    {
        $userId = new UserId(Uuid::uuid4()->toString());

        $company = (new CompanyBuilder())
            ->withUserId($userId)
            ->build();

        $events = $company->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CompanyAdded::class, $events[0]);
        self::assertSame($userId->getValue(), $events[0]->userId->getValue());
    }

    public function testRename(): void
    {
        $company = (new CompanyBuilder())
            ->withName(Name::fromString('Старое название'))
            ->build();

        $newName = Name::fromString('Новое название');
        $company->rename($newName);

        self::assertSame($newName->getValue(), $company->getName()->getValue());
    }

    public function testRenameToSameNameThrowsException(): void
    {
        $name = Name::fromString('Одно и то же название');

        $company = (new CompanyBuilder())
            ->withName($name)
            ->build();

        $this->expectException(DomainException::class);
        $company->rename(Name::fromString('Одно и то же название'));
    }

    public function testChangeInn(): void
    {
        $company = (new CompanyBuilder())
            ->withInn(Inn::fromString('7707083893'))
            ->build();

        $newInn = Inn::fromString('500100732259');
        $company->changeInn($newInn);

        self::assertSame($newInn->getValue(), $company->getInn()->getValue());
    }

    public function testChangeInnToSameValueThrowsException(): void
    {
        $inn = Inn::fromString('7707083893');

        $company = (new CompanyBuilder())
            ->withInn($inn)
            ->build();

        $this->expectException(DomainException::class);
        $company->changeInn(Inn::fromString('7707083893'));
    }

    // -------------------------------------------------------------------------
    // Тесты генерации событий
    // -------------------------------------------------------------------------

    public function testRenameRecordsCompanyRenamedEvent(): void
    {
        $company = (new CompanyBuilder())
            ->withName(Name::fromString('Старое название'))
            ->build();

        // Сбросим событие CompanyAdded, записанное при create()
        $company->releaseEvents();

        $newName = Name::fromString('Новое название');
        $company->rename($newName);

        $events = $company->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CompanyRenamed::class, $events[0]);
        self::assertSame($newName->getValue(), $events[0]->name->getValue());
    }

    public function testChangeInnRecordsCompanyInnChangedEvent(): void
    {
        $company = (new CompanyBuilder())
            ->withInn(Inn::fromString('7707083893'))
            ->build();

        // Сбросим событие CompanyAdded, записанное при create()
        $company->releaseEvents();

        $newInn = Inn::fromString('500100732259');
        $company->changeInn($newInn);

        $events = $company->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CompanyInnChanged::class, $events[0]);
        self::assertSame($newInn->getValue(), $events[0]->inn->getValue());
    }

    // -------------------------------------------------------------------------
    // Тесты архивации (мягкое удаление)
    // -------------------------------------------------------------------------

    public function testArchive(): void
    {
        $company = (new CompanyBuilder())->build();

        self::assertFalse($company->isArchived());

        $company->archive();

        self::assertTrue($company->isArchived());
    }

    public function testArchiveAlreadyArchivedThrowsException(): void
    {
        $company = (new CompanyBuilder())->build();
        $company->archive();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Company is already archived.');

        $company->archive();
    }

    public function testArchiveRecordsCompanyArchivedEvent(): void
    {
        $company = (new CompanyBuilder())->build();

        // Сбросим событие CompanyAdded, записанное при create()
        $company->releaseEvents();

        $company->archive();

        $events = $company->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(CompanyArchived::class, $events[0]);
        self::assertSame($company->getId()->getValue(), $events[0]->id->getValue());
    }
}
