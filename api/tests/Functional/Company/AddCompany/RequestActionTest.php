<?php

declare(strict_types=1);

namespace Tests\Functional\Company\AddCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Event\CompanyAdded;
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
    // Тест 1: Успешное создание компании (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $transport->reset();

        $this->client->jsonRequest('POST', '/v1/companies', [
            'name' => 'ООО Ромашка',
            'inn'  => '0901046828',
        ]);

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('id', $data);
        self::assertNotEmpty($data['id']);

        // Компания сохранена в БД
        $company = $this->companies->get(new Id($data['id']));
        self::assertEquals('ООО Ромашка', $company->getName()->getValue());
        self::assertEquals('0901046828', $company->getInn()->getValue());

        // Событие CompanyAdded отправлено в шину
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyAdded::class, $event);
        self::assertEquals('ООО Ромашка', $event->name->getValue());
        self::assertEquals('0901046828', $event->inn->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 2: Ошибка валидации — некорректный ИНН (9 цифр)
    // -------------------------------------------------------------------------

    public function testInvalidInn(): void
    {
        $this->client->jsonRequest('POST', '/v1/companies', [
            'name' => 'ООО Тест',
            'inn'  => '123456789', // 9 цифр — неверно
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('inn', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 3: Ошибка валидации — пустое название компании
    // -------------------------------------------------------------------------

    public function testEmptyName(): void
    {
        $this->client->jsonRequest('POST', '/v1/companies', [
            'name' => '',
            'inn'  => '0901046828',
        ]);

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('name', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 4: Дублирование ИНН — 409 Conflict
    // -------------------------------------------------------------------------

    public function testDuplicateInn(): void
    {
        // RequestFixture уже загрузил компанию с ИНН 7707083893
        $this->client->jsonRequest('POST', '/v1/companies', [
            'name' => 'Другая компания',
            'inn'  => RequestFixture::INN_EXISTS,
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Company with this INN already exists.'], $data);
    }
}
