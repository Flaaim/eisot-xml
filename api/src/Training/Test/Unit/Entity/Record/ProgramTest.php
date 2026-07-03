<?php

declare(strict_types=1);

namespace App\Training\Test\Unit\Entity\Record;

use App\Training\Entity\Record\Program;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ProgramTest extends TestCase
{
    public function testFromIdValid(): void
    {
        $program = Program::fromId(1);

        self::assertEquals(1, $program->getId());
        self::assertEquals('Оказание первой помощи пострадавшим', $program->getTitle());
    }

    public function testCatalogReturns28Items(): void
    {
        self::assertCount(28, Program::catalog());
    }

    public function testAllowedIdsReturns28Items(): void
    {
        self::assertCount(28, Program::allowedIds());
    }

    public function testEachAllowedIdCanBeCreated(): void
    {
        foreach (Program::catalog() as $id => $title) {
            $program = Program::fromId($id);
            self::assertEquals($id, $program->getId());
            self::assertEquals($title, $program->getTitle());
        }
    }

    public function testInvalidIdThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromId(999);
    }

    public function testIdFiveDoesNotExist(): void
    {
        $this->expectException(DomainException::class);

        Program::fromId(5);
    }

    public function testIdZeroThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromId(0);
    }

    public function testFromStringValid(): void
    {
        $program = Program::fromString('Оказание первой помощи пострадавшим');

        self::assertEquals(1, $program->getId());
        self::assertEquals('Оказание первой помощи пострадавшим', $program->getTitle());
    }

    public function testFromStringInvalidThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromString('Несуществующая программа');
    }

    public function testFromStringEmptyThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromString('');
    }

    public function testEquality(): void
    {
        $a = Program::fromId(9);
        $b = Program::fromId(9);

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Program::fromId(1);
        $b = Program::fromId(2);

        self::assertFalse($a->isEqualTo($b));
    }

    public function testGetValueReturnsTitle(): void
    {
        $program = Program::fromId(29);

        self::assertEquals('Безопасные методы и приемы работ в театрах', $program->getTitle());
    }

    public function testToStringReturnsTitle(): void
    {
        $program = Program::fromId(1);

        self::assertEquals('Оказание первой помощи пострадавшим', (string)$program);
    }
}
