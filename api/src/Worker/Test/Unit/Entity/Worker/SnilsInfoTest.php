<?php

declare(strict_types=1);

namespace App\Worker\Test\Unit\Entity\Worker;

use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class SnilsInfoTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Гражданин РФ
    // -------------------------------------------------------------------------

    public function testCitizenWithSnils(): void
    {
        $snils = Snils::fromString('112-233-445 95');
        $info  = SnilsInfo::forCitizen($snils);

        self::assertFalse($info->isForeigner());
        self::assertNotNull($info->getSnils());
        self::assertEquals('112-233-445 95', $info->getSnils()->getValue());
        self::assertNull($info->getCitizenship());
        self::assertNull($info->getForeignSnils());
    }

    public function testCitizenWithoutSnilsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('SNILS is required for a citizen of Russia.');

        SnilsInfo::fromPrimitives(false, null, null, null);
    }

    public function testCitizenWithEmptySnilsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('SNILS is required for a citizen of Russia.');

        SnilsInfo::fromPrimitives(false, '', null, null);
    }

    public function testCitizenWithCitizenshipThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Citizenship must be empty for a citizen of Russia.');

        SnilsInfo::fromPrimitives(false, '112-233-445 95', 'Узбекистан', null);
    }

    public function testCitizenWithForeignSnilsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Foreign SNILS must be empty for a citizen of Russia.');

        SnilsInfo::fromPrimitives(false, '112-233-445 95', null, 'ABC123');
    }

    // -------------------------------------------------------------------------
    // Иностранец
    // -------------------------------------------------------------------------

    public function testForeignerWithCitizenship(): void
    {
        $info = SnilsInfo::forForeigner('Узбекистан');

        self::assertTrue($info->isForeigner());
        self::assertNull($info->getSnils());
        self::assertEquals('Узбекистан', $info->getCitizenship());
        self::assertNull($info->getForeignSnils());
    }

    public function testForeignerWithCitizenshipAndForeignSnils(): void
    {
        $info = SnilsInfo::forForeigner('Узбекистан', 'ABC-123-456');

        self::assertTrue($info->isForeigner());
        self::assertNull($info->getSnils());
        self::assertEquals('Узбекистан', $info->getCitizenship());
        self::assertEquals('ABC-123-456', $info->getForeignSnils());
    }

    public function testForeignerWithoutCitizenshipThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Citizenship is required for a foreign worker.');

        SnilsInfo::fromPrimitives(true, null, null, null);
    }

    public function testForeignerWithStandardSnilsThrowsException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Standard SNILS must be empty for a foreign worker.');

        SnilsInfo::fromPrimitives(true, '112-233-445 95', 'Узбекистан', null);
    }

    // -------------------------------------------------------------------------
    // fromPrimitives — round-trip
    // -------------------------------------------------------------------------

    public function testFromPrimitivesCitizen(): void
    {
        $info = SnilsInfo::fromPrimitives(false, '112-233-445 95', null, null);

        self::assertFalse($info->isForeigner());
        self::assertEquals('112-233-445 95', $info->getSnils()->getValue());
    }

    public function testFromPrimitivesForeigner(): void
    {
        $info = SnilsInfo::fromPrimitives(true, null, 'Таджикистан', 'XYZ-999');

        self::assertTrue($info->isForeigner());
        self::assertEquals('Таджикистан', $info->getCitizenship());
        self::assertEquals('XYZ-999', $info->getForeignSnils());
    }
}
