<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompanies;

final class Query
{
    public function __construct(
        public string $userId,
    ) {}
}
