<?php

declare(strict_types=1);

namespace App\Company\Service;

use App\Company\Exception\RemoteException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class CompanyNameFetcher
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        #[Target('dadata.client')]
        private readonly HttpClientInterface $client,
    ) {}

    public function get(string $inn): array
    {
        try {
            $response = $this->client->request('POST', 'findById/party', [
                'json' => ['query' => $inn],
            ]);
            return $response->toArray();
        } catch (Throwable $e) {
            throw new RemoteException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
