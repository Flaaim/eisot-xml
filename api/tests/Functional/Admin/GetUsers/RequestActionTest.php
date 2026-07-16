<?php

declare(strict_types=1);

namespace Tests\Functional\Admin\GetUsers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        $this->adminToken = $this->getAccessToken(
            $this->client,
            RequestFixture::ADMIN_EMAIL,
            RequestFixture::USER_PASS,
        );
        $this->userToken = $this->getAccessToken(
            $this->client,
            RequestFixture::USER_EMAIL,
            RequestFixture::USER_PASS,
        );
    }

    public function testUnauthenticatedReturns401(): void
    {
        $this->client->jsonRequest('GET', '/v1/admin/users');

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testForbiddenForRegularUser(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/admin/users',
            [],
            $this->authHeaders($this->userToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccessForAdmin(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/admin/users',
            [],
            $this->authHeaders($this->adminToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertGreaterThanOrEqual(2, $data['total']);
    }

    public function testStatsSuccessForAdmin(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/admin/stats',
            [],
            $this->authHeaders($this->adminToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('totalUsers', $data);
        self::assertArrayHasKey('activeBasicPlan', $data);
        self::assertArrayHasKey('activeExtendedPlan', $data);
    }
}
