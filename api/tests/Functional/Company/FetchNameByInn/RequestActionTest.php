<?php

declare(strict_types=1);

namespace Tests\Functional\Company\FetchNameByInn;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;
use Tests\Functional\OAuthTokenTrait;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    use OAuthTokenTrait;

    private KernelBrowser $client;
    private readonly ContainerInterface $container;
    private string $ownerToken;
    private string $otherToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();

        $this->container = $this->client->getContainer();
        $fixture = new FixturesLoader($this->container);

        $fixture->loadFixtures([RequestFixture::class]);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);
        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);
    }

    public function testUnauthenticatedReturns401(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/suggestions?inn=' . RequestFixture::COMPANY_INN
        );
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccess(): void
    {
        $mockResponse = new MockResponse('{"suggestions": [{"value": "ПАО СБЕРБАНК"}]}', [
            'http_code' => 200,
        ]);
        $mockClient = new MockHttpClient([$mockResponse]);
        $this->client->disableReboot();

        $this->container->set('dadata.client', $mockClient);

        $this->client->jsonRequest(
            'GET',
            '/v1/companies/suggestions?inn=' . RequestFixture::COMPANY_INN,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['title' => 'ПАО СБЕРБАНК'], $data);
    }

    public function testFailed(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 500,
        ]);
        $mockClient = new MockHttpClient([$mockResponse]);
        $this->client->disableReboot();

        $this->container->set('dadata.client', $mockClient);
        $untestedInn = '0000000000';
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/suggestions?inn=' . $untestedInn,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(Response::HTTP_BAD_GATEWAY, $this->client->getResponse()->getStatusCode());
    }

    public function testInvalid(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/suggestions?inn=',
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['errors' => [
            'inn' => 'This value should not be blank.',
        ]], $data);
    }
}
