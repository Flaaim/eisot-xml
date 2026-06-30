<?php

declare(strict_types=1);

namespace Tests\Functional\Company\GetCompany;

use App\Company\Entity\Company\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;
use Tests\Functional\OAuthTokenTrait;

final class RequestActionTest extends WebTestCase
{
    use OAuthTokenTrait;
    private readonly KernelBrowser $client;
    private CompanyRepository $companies;
    private string $ownerToken;
    private string $otherToken;
    public function setUp(): void
    {
        $this->client = static::createClient();
        $container    = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->companies = new CompanyRepository($em);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);

        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);
    }

    // -------------------------------------------------------------------------
    // Тест 0: Без авторизации — 401
    // -------------------------------------------------------------------------
    public function testUnauthenticatedReturns401(): void
    {
        $this->client->jsonRequest(
          'GET',
          '/v1/companies/' . RequestFixture::COMPANY_ID
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }


    public function testSuccess(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            server: $this->authHeaders($this->ownerToken)
        );
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals([
            'id' => RequestFixture::COMPANY_ID,
            'name' => RequestFixture::COMPANY_NAME,
            'inn' => RequestFixture::COMPANY_INN,
            'status' => 'ACTIVE',
        ], $data);
    }
    public function testNotFoundToNotOwner(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            server: $this->authHeaders($this->otherToken)
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Company not found.'], $data);
    }

    public function testNotFoundCompanyReturnsError(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/companies/' . RequestFixture::COMPANY_NOT_FOUND_ID,
            server: $this->authHeaders($this->ownerToken)
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Company not found.'], $data);
    }
}
