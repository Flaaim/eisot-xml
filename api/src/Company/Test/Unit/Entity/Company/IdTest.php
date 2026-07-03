<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Entity\Company;

use App\Company\Entity\Company\Id;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
final class IdTest extends TestCase
{
    public function testSuccess(): void
    {
        $id = new Id($value = Uuid::uuid4()->toString());

        self::assertSame($value, $id->getValue());
    }

    public function testGenerate(): void
    {
        $id = Id::generate();

        self::assertNotEmpty($id->getValue());
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $id->getValue()
        );
    }

    public function testGenerateIsUnique(): void
    {
        $id1 = Id::generate();
        $id2 = Id::generate();

        self::assertNotEquals($id1->getValue(), $id2->getValue());
    }

    public function testIsLowercased(): void
    {
        $value = Uuid::uuid4()->toString();

        $id = new Id(mb_strtoupper($value));

        self::assertSame($value, $id->getValue());
    }

    public function testToString(): void
    {
        $id = new Id($value = Uuid::uuid4()->toString());

        self::assertSame($value, (string)$id);
    }

    public function testIncorrectValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('not-a-uuid');
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('');
    }
}
