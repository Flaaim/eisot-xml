<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Entity\Company;

use App\Company\Entity\Company\Inn;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class InnTest extends TestCase
{
    public function testSuccessLegalEntity(): void
    {
        $inn = Inn::fromString($value = '7707083893');

        self::assertSame($value, $inn->getValue());
    }

    public function testSuccessIndividual(): void
    {
        $inn = Inn::fromString($value = '500100732259');

        self::assertSame($value, $inn->getValue());
    }

    public function testIsLegalForTenDigits(): void
    {
        $inn = Inn::fromString('7707083893');

        self::assertTrue($inn->isLegal());
        self::assertFalse($inn->isIndividual());
    }

    public function testIsIndividualForTwelveDigits(): void
    {
        $inn = Inn::fromString('500100732259');

        self::assertTrue($inn->isIndividual());
        self::assertFalse($inn->isLegal());
    }

    public function testIsEqualToSame(): void
    {
        $inn1 = Inn::fromString('7707083893');
        $inn2 = Inn::fromString('7707083893');

        self::assertTrue($inn1->isEqualTo($inn2));
    }

    public function testIsEqualToDifferent(): void
    {
        $inn1 = Inn::fromString('7707083893');
        $inn2 = Inn::fromString('7736050003');

        self::assertFalse($inn1->isEqualTo($inn2));
    }

    public function testToString(): void
    {
        $inn = Inn::fromString($value = '7707083893');

        self::assertSame($value, (string) $inn);
    }

    public function testTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('123456789');
    }

    public function testTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('1234567890123');
    }

    public function testElevenDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('12345678901');
    }

    public function testContainsLetters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('770708389X');
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('');
    }

    public function testInvalidChecksumLegalEntity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('1234567890');
    }

    public function testInvalidChecksumIndividual(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Inn::fromString('123456789012');
    }
}
