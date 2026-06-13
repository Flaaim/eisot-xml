<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ChangeCompanyInn;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Event\CompanyInnChanged;
use Doctrine\ORM\EntityManagerInterface;
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
    private CompanyRepository $companies;
    private string $ownerToken;
    private string $otherToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
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
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => '0901046828'],
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 1: Чужой пользователь не может изменить ИНН — 403
    // -------------------------------------------------------------------------

    public function testForbiddenForNonOwner(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => '0901046828'],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('message', $data);
    }

    // -------------------------------------------------------------------------
    // Тест 2: Успешная смена ИНН владельцем (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $newInn = '0901046828';

        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => $newInn],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        // ИНН обновлён в БД
        $company = $this->companies->get(new Id(RequestFixture::COMPANY_ID));
        self::assertEquals($newInn, $company->getInn()->getValue());

        // Событие CompanyInnChanged отправлено в шину
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyInnChanged::class, $event);
        self::assertEquals($newInn, $event->inn->getValue());
        self::assertEquals(RequestFixture::COMPANY_ID, $event->id->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 3: Доменный инвариант — тот же ИНН (409 Conflict)
    // -------------------------------------------------------------------------

    public function testSameInnThrowsConflict(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => RequestFixture::COMPANY_INN],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company with this INN already exists.'], $data);

        $transport = $this->client->getContainer()->get('messenger.transport.async');
        self::assertCount(0, $transport->getSent());
    }

    // -------------------------------------------------------------------------
    // Тест 4: ИНН уже занят другой компанией (409 Conflict)
    // -------------------------------------------------------------------------

    public function testDuplicateInnThrowsConflict(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => RequestFixture::INN_EXISTS],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company with this INN already exists.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 5: Валидация — неверный формат ИНН (422)
    // -------------------------------------------------------------------------

    public function testInvalidInnFormatReturnsValidationError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/inn',
            ['inn' => '12345'], // 5 цифр — неверно
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('inn', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 6: Компания не найдена (409)
    // -------------------------------------------------------------------------

    public function testNotFoundCompanyReturnsError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/7d8a7fb1-35c5-443a-af1e-9b9ed80c563f/inn',
            ['inn' => '0901046828'],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is not found.'], $data);
    }
}
