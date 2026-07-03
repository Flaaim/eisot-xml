<?php

declare(strict_types=1);

namespace App\Company\Test\Unit\Query\GetCompanies;

use App\Company\Query\GetCompanies\CompanyShortDTO;
use App\Company\Query\GetCompanies\Handler;
use App\Company\Query\GetCompanies\Query;
use App\Company\ReadModel\CompanyFetcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class HandlerTest extends TestCase
{
    private CompanyFetcherInterface&MockObject $fetcher;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->fetcher = $this->createMock(CompanyFetcherInterface::class);
        $this->handler = new Handler($this->fetcher);
    }

    public function testReturnsEmptyArrayWhenNoCompaniesFound(): void
    {
        $userId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $this->fetcher
            ->expects(self::once())
            ->method('findAllByUserId')
            ->with($userId)
            ->willReturn([]);

        $query = new Query($userId);
        $result = $this->handler->handle($query);

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testReturnsListOfCompanyDtos(): void
    {
        $userId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $rows = [
            [
                'id'               => '11111111-1111-1111-1111-111111111111',
                'name'             => 'ООО «Альфа»',
                'inn'              => '7707083893',
                'status'           => 'ACTIVE',
                'workers_count'    => 3,
                'protocols_count'  => 5,
            ],
            [
                'id'               => '22222222-2222-2222-2222-222222222222',
                'name'             => 'ИП Иванов',
                'inn'              => '771234567890',
                'status'           => 'ARCHIVED',
                'workers_count'    => 0,
                'protocols_count'  => 0,
            ],
        ];

        $this->fetcher
            ->expects(self::once())
            ->method('findAllByUserId')
            ->with($userId)
            ->willReturn($rows);

        $query = new Query($userId);
        $result = $this->handler->handle($query);

        self::assertCount(2, $result);

        self::assertInstanceOf(CompanyShortDTO::class, $result[0]);
        self::assertSame('11111111-1111-1111-1111-111111111111', $result[0]->id);
        self::assertSame('7707083893', $result[0]->inn);
        self::assertSame('ACTIVE', $result[0]->status);
        self::assertSame(3, $result[0]->workersCount);
        self::assertSame(5, $result[0]->protocolsCount);

        self::assertInstanceOf(CompanyShortDTO::class, $result[1]);
        self::assertSame('22222222-2222-2222-2222-222222222222', $result[1]->id);
        self::assertSame('771234567890', $result[1]->inn);
        self::assertSame('ARCHIVED', $result[1]->status);
        self::assertSame(0, $result[1]->workersCount);
        self::assertSame(0, $result[1]->protocolsCount);
    }

    public function testPassesCorrectUserIdToFetcher(): void
    {
        $userId = 'specific-user-uuid-1234-5678';

        $this->fetcher
            ->expects(self::once())
            ->method('findAllByUserId')
            ->with(self::callback(static fn (string $passedId): bool => $passedId === $userId))
            ->willReturn([]);

        $this->handler->handle(new Query($userId));
    }
}
