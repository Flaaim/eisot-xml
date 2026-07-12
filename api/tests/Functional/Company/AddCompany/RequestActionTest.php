<?php

declare(strict_types=1);

namespace Tests\Functional\Company\AddCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Entity\Company\Id;
use App\Company\Event\CompanyAdded;
use App\Subscription\Entity\Subscription\Id as SubscriptionId;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\Subscription;
use App\Subscription\Entity\Subscription\UserId as SubscriptionUserId;
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
    private string $accessToken;

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

        // Получаем токен владельца
        $this->accessToken = $this->getAccessToken(
            $this->client,
            RequestFixture::USER_EMAIL,
            RequestFixture::USER_PASS,
        );
    }

    // -------------------------------------------------------------------------
    // Тест 0: Без авторизации — 401
    // -------------------------------------------------------------------------

    public function testUnauthenticatedReturns401(): void
    {
        $this->client->jsonRequest('POST', '/v1/companies', [
            'name' => 'ООО Ромашка',
            'inn'  => '0901046828',
        ]);

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 1: Успешное создание компании (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccess(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => 'ООО Ромашка', 'inn' => '7736050003'],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('id', $data);
        self::assertNotEmpty($data['id']);

        // Компания сохранена в БД
        $company = $this->companies->get(new Id($data['id']));
        self::assertEquals('ООО Ромашка', $company->getName()->getValue());
        self::assertEquals('7736050003', $company->getInn()->getValue());
        // userId привязан к текущему пользователю
        self::assertEquals(RequestFixture::USER_ID, $company->getUserId()->getValue());

        // Событие CompanyAdded отправлено в шину с userId
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyAdded::class, $event);
        self::assertEquals('ООО Ромашка', $event->name->getValue());
        self::assertEquals('7736050003', $event->inn->getValue());
        self::assertEquals(RequestFixture::USER_ID, $event->userId->getValue());
    }

    // -------------------------------------------------------------------------
    // Тест 2: Ошибка валидации — некорректный ИНН (9 цифр)
    // -------------------------------------------------------------------------

    public function testInvalidInn(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => 'ООО Тест', 'inn' => '123456789'],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('inn', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // Тест 2b: Ошибка валидации — неверная контрольная сумма (10 цифр)
    // -------------------------------------------------------------------------

    public function testInvalidInnChecksum(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => 'ООО Тест', 'inn' => '1234567890'],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('errors', $data);
        self::assertArrayHasKey('inn', $data['errors']);
        self::assertEquals('INN checksum is invalid.', $data['errors']['inn']);
    }

    // -------------------------------------------------------------------------
    // Тест 3: Ошибка валидации — пустое название компании
    // -------------------------------------------------------------------------

    public function testEmptyName(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => '', 'inn' => '0901046828'],
            $this->authHeaders($this->accessToken),
        );

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
        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => 'Другая компания', 'inn' => RequestFixture::INN_EXISTS],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Company with this INN already exists.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 5: Лимит компаний для Basic Plan — 403 Forbidden
    // -------------------------------------------------------------------------

    public function testCompanyLimitReachedForBasicPlan(): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $subscriptionRepo = $em->getRepository(Subscription::class);
        $existingSubscriptions = $subscriptionRepo->findAll();
        foreach ($existingSubscriptions as $existingSubscription) {
            $em->remove($existingSubscription);
        }
        $em->flush();

        $subscription = Subscription::activate(
            SubscriptionId::generate(),
            new SubscriptionUserId(RequestFixture::USER_ID),
            Plan::BASIC,
            Period::fromDurationDays(30),
        );
        $em->persist($subscription);
        $em->flush();

        $this->client->jsonRequest(
            'POST',
            '/v1/companies',
            ['name' => 'Вторая компания', 'inn' => '7736050003'],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals('company_limit_reached', $data['code']);
    }
}
