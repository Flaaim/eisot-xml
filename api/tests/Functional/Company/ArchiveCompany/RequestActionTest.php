<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ArchiveCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Event\CompanyArchived;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    private KernelBrowser $client;
    private CompanyRepository $companies;

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
    }

    // -------------------------------------------------------------------------
    // Тест 1: Успешная архивация (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Компания помечена как архивированная в БД
        // Очищаем identity map, чтобы получить свежие данные из БД
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $company = $this->companies->get(new Id(RequestFixture::COMPANY_ID));
        self::assertTrue($company->isArchived());

        // Событие CompanyArchived отправлено в шину
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyArchived::class, $event);
        self::assertEquals(RequestFixture::COMPANY_ID, $event->id->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 2: Защита инварианта — повторная архивация (409 Conflict)
    // -------------------------------------------------------------------------

    public function testAlreadyArchivedReturnsConflict(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        // Первый запрос — архивируем
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
        );
        self::assertEquals(204, $this->client->getResponse()->getStatusCode());
        // После первой архивации в шину ушло 1 событие;
        // при повторном запросе новых событий быть не должно
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        // Второй запрос — агрегат защищает инвариант
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is already archived.'], $data);

    }

    // -------------------------------------------------------------------------
    // Тест 3: Компания не найдена (409)
    // -------------------------------------------------------------------------

    public function testNotFoundReturnsError(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/dd8b1f8d-3cca-40f2-b21d-81c81cbf9579',
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is not found.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 4: Архивированная компания не входит в список активных
    // -------------------------------------------------------------------------

    public function testArchivedCompanyIsExcludedFromActiveList(): void
    {
        // Архивируем компанию
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID,
        );
        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        // Очищаем identity map для получения актуальных данных
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        // Проверяем, что в списке активных компании нет
        $activeCompanies = $this->companies->findAllActive();
        $ids = array_map(
            static fn($c) => $c->getId()->getValue(),
            $activeCompanies,
        );

        self::assertNotContains(RequestFixture::COMPANY_ID, $ids);
    }
}
