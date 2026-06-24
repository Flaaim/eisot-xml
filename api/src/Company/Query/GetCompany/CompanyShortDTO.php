<?php

declare(strict_types=1);

namespace App\Company\Query\GetCompany;

final readonly class CompanyShortDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $inn,
        public bool $is_archived,
    ) {}
}
