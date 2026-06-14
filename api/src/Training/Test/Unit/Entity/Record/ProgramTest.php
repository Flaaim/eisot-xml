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
    public function testValidProgram(): void
    {
        $program = Program::fromString('1. Оказание первой помощи пострадавшим');

        self::assertEquals('1. Оказание первой помощи пострадавшим', $program->getValue());
    }

    public function testAllowedProgramsReturns28Items(): void
    {
        self::assertCount(28, Program::allowedPrograms());
    }

    public function testEachAllowedProgramCanBeCreated(): void
    {
        foreach (Program::allowedPrograms() as $name) {
            $program = Program::fromString($name);
            self::assertEquals($name, $program->getValue());
        }
    }

    public function testInvalidProgramThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromString('Несуществующая программа');
    }

    public function testEmptyProgramThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Program::fromString('');
    }

    public function testEquality(): void
    {
        $a = Program::fromString('9. Безопасные методы и приемы выполнения работ на высоте');
        $b = Program::fromString('9. Безопасные методы и приемы выполнения работ на высоте');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Program::fromString('1. Оказание первой помощи пострадавшим');
        $b = Program::fromString('2. Использование (применение) средств индивидуальной защиты');

        self::assertFalse($a->isEqualTo($b));
    }

    public function testLongProgramName(): void
    {
        $program = Program::fromString(
            '26. Безопасные методы и приемы работ по перемещению тяжеловесных и крупногабаритных грузов при отсутствии машин соответствующей грузоподъемности и разборке покосившихся и опасных (неправильно уложенных) штабелей круглых лесоматериалов'
        );

        self::assertNotEmpty($program->getValue());
    }
}
