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
    public function testValidSnils(): void
    {
        $snils = Snils::fromString('112-233-445 95');

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

    public function testInvalidChecksumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SNILS checksum is invalid');

        Snils::fromString('112-233-445 99');
    }

    public function testInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::fromString('12345678901');
    }

    public function testTooShortThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::fromString('112-233-445');
    }

    public function testNormalizeFormatsDigits(): void
    {
        self::assertSame('112-233-445 95', Snils::normalize('11223344595'));
        self::assertSame('112-233-445', Snils::normalize('112233445'));
    }

    public function testCalculateChecksum(): void
    {
        self::assertSame(95, Snils::calculateChecksum('112233445'));
    }

    public function testEquality(): void
    {
        $a = Snils::fromString('112-233-445 95');
        $b = Snils::fromString('112-233-445 95');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Snils::fromString('112-233-445 95');
        $b = Snils::fromString('001-001-001 00');

        self::assertFalse($a->isEqualTo($b));
    }
}
