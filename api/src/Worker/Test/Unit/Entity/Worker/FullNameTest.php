<?php

declare(strict_types=1);

namespace App\Worker\Test\Unit\Entity\Worker;

use App\Worker\Entity\Worker\FullName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FullNameTest extends TestCase
{
    public function testCreateWithMiddleName(): void
    {
        $name = FullName::create('Иванов', 'Иван', 'Иванович');

        self::assertEquals('Иванов', $name->getLastName());
        self::assertEquals('Иван', $name->getFirstName());
        self::assertEquals('Иванович', $name->getMiddleName());
        self::assertEquals('Иванов Иван Иванович', $name->getFull());
    }

    public function testCreateWithoutMiddleName(): void
    {
        $name = FullName::create('Ali', 'Mohammed');

        self::assertEquals('Ali', $name->getLastName());
        self::assertEquals('Mohammed', $name->getFirstName());
        self::assertNull($name->getMiddleName());
        self::assertEquals('Ali Mohammed', $name->getFull());
    }

    public function testEmptyLastNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FullName::create('', 'Иван');
    }

    public function testEmptyFirstNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FullName::create('Иванов', '');
    }

    public function testEquality(): void
    {
        $a = FullName::create('Иванов', 'Иван', 'Иванович');
        $b = FullName::create('Иванов', 'Иван', 'Иванович');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = FullName::create('Иванов', 'Иван', 'Иванович');
        $b = FullName::create('Петров', 'Пётр', 'Петрович');

        self::assertFalse($a->isEqualTo($b));
    }

    public function testEmptyMiddleNameTreatedAsNull(): void
    {
        $name = FullName::create('Иванов', 'Иван', '');

        self::assertNull($name->getMiddleName());
        self::assertEquals('Иванов Иван', $name->getFull());
    }
}
