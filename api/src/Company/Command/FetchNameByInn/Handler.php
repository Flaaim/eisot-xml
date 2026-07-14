<?php

declare(strict_types=1);

namespace App\Company\Command\FetchNameByInn;

use App\Company\Service\CompanyNameFetcher;
use DomainException;

final class Handler
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private readonly CompanyNameFetcher $companyNameFetcher,
    ) {}

    public function handle(Command $command): string
    {
        $inn = $command->inn;

        $dadataResult = $this->companyNameFetcher->get($inn);

        if (!empty($dadataResult['suggestions'][0]['value'])) {
            return $dadataResult['suggestions'][0]['value'];
        }

        throw new DomainException('Unable to find name for inn: ' . $inn);
    }
}
