<?php

declare(strict_types=1);

namespace Tests\Functional\Company\RenameCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Event\CompanyRenamed;
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
    // Тест 1: Успешное переименование (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/name',
            ['name' => 'ООО Новое Название'],
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Название обновлено в БД
        $company = $this->companies->get(new Id(RequestFixture::COMPANY_ID));
        self::assertEquals('ООО Новое Название', $company->getName()->getValue());

        // Событие CompanyRenamed отправлено в шину
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyRenamed::class, $event);
        self::assertEquals('ООО Новое Название', $event->name->getValue());
        self::assertEquals(RequestFixture::COMPANY_ID, $event->id->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 2: Доменный инвариант — то же самое название (409 Conflict)
    // -------------------------------------------------------------------------

    public function testSameNameThrowsConflict(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/name',
            ['name' => RequestFixture::COMPANY_NAME],
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company already has this name.'], $data);

        // Никакие события не сгенерированы
        self::assertCount(0, $transport->getSent());
    }

    // -------------------------------------------------------------------------
    // Тест 3: Валидация — пустое название (422)
    // -------------------------------------------------------------------------

    public function testEmptyNameReturnsValidationError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/name',
            ['name' => ''],
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('name', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 4: Валидация — название длиннее 500 символов (422)
    // -------------------------------------------------------------------------

    public function testTooLongNameReturnsValidationError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/name',
            ['name' => str_repeat('А', 501)],
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('name', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 5: Компания не найдена (409)
    // -------------------------------------------------------------------------

    public function testNotFoundCompanyReturnsError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/companies/7d8a7fb1-35c5-443a-af1e-9b9ed80c563f/name',
            ['name' => 'ООО Тест'],
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is not found.'], $data);
    }
}
