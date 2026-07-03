<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompany;

use App\Company\ReadModel\CompanyFetcherInterface;
use DomainException;

final class Handler
{
    public function __construct(
        private readonly CompanyFetcherInterface $fetcher,
    ) {}

    /** @psalm-suppress PossiblyUnusedMethod */
    public function handle(Query $query): CompanyShortDTO
    {
        $row = $this->fetcher->findOneByUserId($query->id, $query->userId);

        if (empty($row)) {
            throw new DomainException('Company not found.');
        }

        return new CompanyShortDTO(
            $row['id'],
            $row['name'],
            $row['inn'],
            $row['status']
        );
    }
}
