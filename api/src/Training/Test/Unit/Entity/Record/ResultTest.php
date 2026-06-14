<?php

declare(strict_types=1);

namespace App\Training\Test\Unit\Entity\Record;

use App\Training\Entity\Record\Result;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class ResultTest extends TestCase
{
    public function testSatisfactory(): void
    {
        $result = Result::satisfactory();

        self::assertEquals('удовлетворительно', $result->getValue());
        self::assertTrue($result->isSatisfactory());
    }

    public function testUnsatisfactory(): void
    {
        $result = Result::unsatisfactory();

        self::assertEquals('неудовлетворительно', $result->getValue());
        self::assertFalse($result->isSatisfactory());
    }

    public function testFromStringSatisfactory(): void
    {
        $result = Result::fromString('удовлетворительно');

        self::assertTrue($result->isSatisfactory());
    }

    public function testFromStringUnsatisfactory(): void
    {
        $result = Result::fromString('неудовлетворительно');

        self::assertFalse($result->isSatisfactory());
    }

    public function testInvalidResultThrowsException(): void
    {
        $this->expectException(DomainException::class);

        Result::fromString('отлично');
    }

    public function testEquality(): void
    {
        $a = Result::satisfactory();
        $b = Result::fromString('удовлетворительно');

        self::assertTrue($a->isEqualTo($b));
    }

    public function testInequality(): void
    {
        $a = Result::satisfactory();
        $b = Result::unsatisfactory();

        self::assertFalse($a->isEqualTo($b));
    }
}
