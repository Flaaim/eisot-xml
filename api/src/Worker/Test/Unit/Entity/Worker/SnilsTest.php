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
        $snils = Snils::fromString('644-670-185 07');

        self::assertEquals('644-670-185 07', $snils->getValue());
    }

    public function testInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::fromString('12345678901');
    }

    public function testTooShortThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Snils::fromString('644-670-185');
    }

    public function testEquality(): void
    {
        $a = Snils::fromString('644-670-185 07');
        $b = Snils::fromString('644-670-185 07');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Snils::fromString('644-670-185 07');
        $b = Snils::fromString('123-456-789 12');

        self::assertFalse($a->isEqualTo($b));
    }
}
