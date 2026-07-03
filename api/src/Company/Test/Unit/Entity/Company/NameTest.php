<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Entity\Company;

use App\Company\Entity\Company\Name;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class NameTest extends TestCase
{
    public function testSuccess(): void
    {
        $name = Name::fromString($value = 'ООО Рога и Копыта');

        self::assertSame($value, $name->getValue());
    }

    public function testSuccessWithConstructor(): void
    {
        $name = new Name($value = 'Acme Corp');

        self::assertSame($value, $name->getValue());
    }

    public function testToString(): void
    {
        $name = Name::fromString($value = 'Acme Corp');

        self::assertSame($value, (string)$name);
    }

    public function testIsEqualToSame(): void
    {
        $name1 = Name::fromString('Acme Corp');
        $name2 = Name::fromString('Acme Corp');

        self::assertTrue($name1->isEqualTo($name2));
    }

    public function testIsEqualToDifferent(): void
    {
        $name1 = Name::fromString('Acme Corp');
        $name2 = Name::fromString('Other Corp');

        self::assertFalse($name1->isEqualTo($name2));
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Name::fromString('');
    }

    public function testTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Name::fromString(str_repeat('a', 501));
    }

    public function testMaxAllowedLength(): void
    {
        $name = Name::fromString(str_repeat('a', 500));

        self::assertSame(500, \strlen($name->getValue()));
    }
}
