<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Entity\Company;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Test\Builder\CompanyBuilder;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CompanyTest extends TestCase
{
    public function testCreate(): void
    {
        $company = (new CompanyBuilder())->build();

        self::assertNotNull($company->getId());
        self::assertNotNull($company->getName());
        self::assertNotNull($company->getInn());
    }

    public function testCreateStoresCorrectValues(): void
    {
        $id   = Id::generate();
        $name = Name::fromString('ООО Тест');
        $inn  = Inn::fromString('7707083893');

        $company = (new CompanyBuilder())
            ->withId($id)
            ->withName($name)
            ->withInn($inn)
            ->build();

        self::assertSame($id->getValue(), $company->getId()->getValue());
        self::assertSame($name->getValue(), $company->getName()->getValue());
        self::assertSame($inn->getValue(), $company->getInn()->getValue());
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
}
