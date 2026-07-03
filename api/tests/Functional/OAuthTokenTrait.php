<?php

declare(strict_types=1);

namespace Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Трейт для получения OAuth Bearer-токена в функциональных тестах.
 */
trait OAuthTokenTrait
{
    /**
     * Получает Bearer-токен для указанного пользователя через /token.
     */
    private function getAccessToken(
        KernelBrowser $client,
        string $email,
        string $password,
        string $clientId     = 'frontend',
        string $clientSecret = 'my-super-secret-123',
    ): string {
        $client->request('POST', '/token', [
            'grant_type'    => 'password',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'username'      => $email,
            'password'      => $password,
        ]);

        $data = Json::decode((string)$client->getResponse()->getContent());

        return $data['access_token'];
    }

    /**
     * Формирует массив серверных переменных для авторизованного запроса.
     */
    private function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
    }
}
