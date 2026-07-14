<?php

declare(strict_types=1);

namespace App\Company\Service;

interface CompanyNameFetcherInterface
{
    public function getCompanyName(string $inn): array;
}
