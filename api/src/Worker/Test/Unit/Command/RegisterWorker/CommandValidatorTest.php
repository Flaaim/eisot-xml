<?php

declare(strict_types=1);

namespace App\Worker\Test\Unit\Command\RegisterWorker;

use App\Worker\Command\RegisterWorker\Command;
use App\Worker\Command\RegisterWorker\CommandValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CommandValidatorTest extends TestCase
{
    private CommandValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CommandValidator();
    }

    public function testValidCitizenCommand(): void
    {
        $command = new Command(
            workerId: '550e8400-e29b-41d4-a716-446655440000',
            companyId: '550e8400-e29b-41d4-a716-446655440001',
            userId: '550e8400-e29b-41d4-a716-446655440002',
            lastName: 'Иванов',
            firstName: 'Иван',
            middleName: null,
            profession: 'Электромонтёр',
            isForeigner: false,
            snils: '112-233-445 95',
            citizenship: null,
            foreignSnils: null,
        );

        $this->validator->validate($command);

        self::assertTrue(true);
    }

    public function testCitizenWithoutSnilsThrowsException(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('SNILS is required for a citizen of Russia.');

        $command = new Command(
            workerId: '550e8400-e29b-41d4-a716-446655440000',
            companyId: '550e8400-e29b-41d4-a716-446655440001',
            userId: '550e8400-e29b-41d4-a716-446655440002',
            lastName: 'Иванов',
            firstName: 'Иван',
            middleName: null,
            profession: 'Электромонтёр',
            isForeigner: false,
            snils: null,
            citizenship: null,
            foreignSnils: null,
        );

        $this->validator->validate($command);
    }

    public function testCitizenWithInvalidChecksumThrowsException(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('SNILS checksum is invalid');

        $command = new Command(
            workerId: '550e8400-e29b-41d4-a716-446655440000',
            companyId: '550e8400-e29b-41d4-a716-446655440001',
            userId: '550e8400-e29b-41d4-a716-446655440002',
            lastName: 'Иванов',
            firstName: 'Иван',
            middleName: null,
            profession: 'Электромонтёр',
            isForeigner: false,
            snils: '112-233-445 99',
            citizenship: null,
            foreignSnils: null,
        );

        $this->validator->validate($command);
    }

    public function testValidForeignerCommand(): void
    {
        $command = new Command(
            workerId: '550e8400-e29b-41d4-a716-446655440000',
            companyId: '550e8400-e29b-41d4-a716-446655440001',
            userId: '550e8400-e29b-41d4-a716-446655440002',
            lastName: 'Karimov',
            firstName: 'Ali',
            middleName: null,
            profession: 'Сварщик',
            isForeigner: true,
            snils: null,
            citizenship: 'Узбекистан',
            foreignSnils: 'UZ-123456',
        );

        $this->validator->validate($command);

        self::assertTrue(true);
    }

    public function testForeignerWithStandardSnilsThrowsException(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Standard SNILS must be empty for a foreign worker.');

        $command = new Command(
            workerId: '550e8400-e29b-41d4-a716-446655440000',
            companyId: '550e8400-e29b-41d4-a716-446655440001',
            userId: '550e8400-e29b-41d4-a716-446655440002',
            lastName: 'Karimov',
            firstName: 'Ali',
            middleName: null,
            profession: 'Сварщик',
            isForeigner: true,
            snils: '112-233-445 95',
            citizenship: 'Узбекистан',
            foreignSnils: null,
        );

        $this->validator->validate($command);
    }
}
