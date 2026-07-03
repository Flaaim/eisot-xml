<?php

declare(strict_types=1);

namespace App\Worker\Test\Unit\Entity\Worker;

use App\Worker\Entity\Worker\Snils;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class SnilsTest extends TestCase
{
    public function testValidSnilsFromFormattedString(): void
    {
        $snils = Snils::fromString('112-233-445 95');

        self::assertEquals('112-233-445 95', $snils->getValue());
    }

    public function testValidSnilsFromElevenDigits(): void
    {
        $snils = Snils::fromString('11223344595');

        self::assertEquals('112-233-445 95', $snils->getValue());
    }

    public function testValidSnilsFromDigits(): void
    {
        $snils = Snils::fromDigits('11223344595');

        self::assertEquals('112-233-445 95', $snils->getValue());
    }

    public function testChecksumExemptNumber(): void
    {
        $snils = Snils::fromString('001-001-001 00');

        self::assertEquals('001-001-001 00', $snils->getValue());
    }

    public function testChecksumExemptUpperBound(): void
    {
        $snils = Snils::fromString('001-001-998 99');

        self::assertEquals('001-001-998 99', $snils->getValue());
    }

    public function testInvalidChecksumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SNILS checksum is invalid');

        Snils::fromString('112-233-445 99');
    }

    public function testTooFewDigitsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::fromString('112-233-445');
    }

    public function testNormalizeExtractsDigitsOnly(): void
    {
        self::assertSame('11223344595', Snils::normalize('112-233-445 95'));
        self::assertSame('112233445', Snils::normalize('112-233-445'));
        self::assertSame('', Snils::normalize(''));
    }

    public function testFormatProducesEisotRepresentation(): void
    {
        self::assertSame('112-233-445 95', Snils::format('11223344595'));
    }

    public function testCalculateChecksum(): void
    {
        self::assertSame(95, Snils::calculateChecksum('112233445'));
    }

    public function testCalculateChecksumWhenSumIs100(): void
    {
        self::assertSame(0, Snils::calculateChecksum('322222223'));
    }

    public function testCalculateChecksumWhenSumIs101(): void
    {
        self::assertSame(0, Snils::calculateChecksum('322222224'));
    }

    public function testCalculateChecksumWhenSumExceeds101(): void
    {
        // 106 % 101 = 5
        self::assertSame(5, Snils::calculateChecksum('322222229'));
    }

    public function testCalculateChecksumRequiresExactlyNineDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::calculateChecksum('11223344528');
    }

    public function testEquality(): void
    {
        $a = Snils::fromString('112-233-445 95');
        $b = Snils::fromString('11223344595');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Snils::fromString('112-233-445 95');
        $b = Snils::fromString('001-001-001 00');

        self::assertFalse($a->isEqualTo($b));
    }
}
