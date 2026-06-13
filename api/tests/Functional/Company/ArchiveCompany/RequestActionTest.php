<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ArchiveCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\UserId;
use App\Company\Event\CompanyArchived;
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
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 1: Чужой пользователь не может архивировать — 403
    // -------------------------------------------------------------------------

    public function testForbiddenForNonOwner(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            [],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('message', $data);
    }

    // -------------------------------------------------------------------------
    // Тест 2: Успешная архивация владельцем (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Компания помечена как архивированная в БД
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $company = $this->companies->get(new Id(RequestFixture::COMPANY_ID));
        self::assertTrue($company->isArchived());

        // Событие CompanyArchived отправлено в шину
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyArchived::class, $event);
        self::assertEquals(RequestFixture::COMPANY_ID, $event->id->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 3: Защита инварианта — повторная архивация (409 Conflict)
    // -------------------------------------------------------------------------

    public function testAlreadyArchivedReturnsConflict(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        // Первый запрос — архивируем
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            [],
            $this->authHeaders($this->ownerToken),
        );
        self::assertEquals(204, $this->client->getResponse()->getStatusCode());
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        self::assertCount(1, $transport->getSent());

        // Второй запрос — агрегат защищает инвариант
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is already archived.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 4: Компания не найдена (409)
    // -------------------------------------------------------------------------

    public function testNotFoundReturnsError(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/dd8b1f8d-3cca-40f2-b21d-81c81cbf9579',
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is not found.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 5: Архивированная компания не входит в список активных по userId
    // -------------------------------------------------------------------------

    public function testArchivedCompanyIsExcludedFromActiveList(): void
    {
        // Архивируем компанию
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
            [],
            $this->authHeaders($this->ownerToken),
        );
        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Очищаем identity map для получения актуальных данных
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        // Проверяем, что в списке активных компании нет (фильтрация по userId)
        $activeCompanies = $this->companies->findAllActiveByUser(new UserId(RequestFixture::USER_ID));
        $ids = array_map(
            static fn($c) => $c->getId()->getValue(),
            $activeCompanies,
        );

        self::assertNotContains(RequestFixture::COMPANY_ID, $ids);
    }
}
