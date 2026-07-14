<?php

declare(strict_types=1);

namespace App\Company\Service;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/** @psalm-suppress UnusedClass */
#[AsDecorator(decorates: CompanyNameFetcher::class)]
final class CacheCompanyNameFetcher implements CompanyNameFetcherInterface
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        #[AutowireDecorated]
        private CompanyNameFetcherInterface $innerCompanyFetcher,
        private readonly CacheInterface $cache,
    ) {}

    public function getCompanyName(string $inn): array
    {
        $cacheKey = \sprintf('dadata_inn_%s', $inn);
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($inn): array {
            $item->expiresAfter(2592000);
            return $this->innerCompanyFetcher->getCompanyName($inn);
        });
    }
}
