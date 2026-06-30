<?php

declare(strict_types=1);

namespace Tests\Functional\Worker\RegisterWorker;

use App\Worker\Entity\Worker\WorkerRepository;
use App\Worker\Event\WorkerRegistered;
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
    private WorkerRepository $workers;
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
        $this->workers = new WorkerRepository($em);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);
        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);
    }

    // -------------------------------------------------------------------------
    // Тест 0: Без авторизации — 401
    // -------------------------------------------------------------------------

    public function testUnauthorized(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'   => 'Иванов',
                'firstName'  => 'Иван',
                'profession' => 'Слесарь',
                'isForeigner' => false,
                'snils' => '112-233-445 95',
            ],
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 1: Чужой пользователь — 403
    // -------------------------------------------------------------------------

    public function testForbiddenForNonOwner(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'   => 'Иванов',
                'firstName'  => 'Иван',
                'profession' => 'Слесарь',
                'isForeigner' => false,
                'snils' => '112-233-445 95',
            ],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 2: Успешная регистрация гражданина РФ (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccessCitizen(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'    => 'Иванов',
                'firstName'   => 'Иван',
                'middleName'  => 'Иванович',
                'profession'  => 'Слесарь',
                'isForeigner' => false,
                'snils'       => '112-233-445 95',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertArrayHasKey('id', $data);
        self::assertNotEmpty($data['id']);

        // Работник сохранён в БД
        $worker = $this->workers->get(new \App\Worker\Entity\Worker\WorkerId($data['id']));
        self::assertEquals('Иванов', $worker->getFullName()->getLastName());
        self::assertEquals('Иван', $worker->getFullName()->getFirstName());
        self::assertEquals('Иванович', $worker->getFullName()->getMiddleName());
        self::assertEquals('Слесарь', $worker->getProfession()->getValue());
        self::assertFalse($worker->getSnilsInfo()->isForeigner());
        self::assertEquals('112-233-445 95', $worker->getSnilsInfo()->getSnils()->getValue());
        self::assertEquals(RequestFixture::COMPANY_ID, $worker->getCompanyId()->getValue());

        // Событие WorkerRegistered отправлено в шину
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(WorkerRegistered::class, $event);
    }

    // -------------------------------------------------------------------------
    // Тест 3: Успешная регистрация иностранца (Happy Path)
    // -------------------------------------------------------------------------

    public function testSuccessForeigner(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'     => 'Алиев',
                'firstName'    => 'Мухаммед',
                'profession'   => 'Электрик',
                'isForeigner'  => true,
                'citizenship'  => 'Узбекистан',
                'foreignSnils' => 'UZ-123456',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        // Работник сохранён в БД
        $worker = $this->workers->get(new \App\Worker\Entity\Worker\WorkerId($data['id']));
        self::assertEquals('Алиев', $worker->getFullName()->getLastName());
        self::assertEquals('Мухаммед', $worker->getFullName()->getFirstName());
        self::assertNull($worker->getFullName()->getMiddleName());
        self::assertTrue($worker->getSnilsInfo()->isForeigner());
        self::assertEquals('Узбекистан', $worker->getSnilsInfo()->getCitizenship());
        self::assertEquals('UZ-123456', $worker->getSnilsInfo()->getForeignSnils());
        self::assertNull($worker->getSnilsInfo()->getSnils());

        // Событие
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);
        self::assertInstanceOf(WorkerRegistered::class, $sent[0]->getMessage());
    }

    // -------------------------------------------------------------------------
    // Тест 4: Иностранец с обычным СНИЛС — нарушение инварианта (409)
    // -------------------------------------------------------------------------

    public function testForeignerWithStandardSnilsThrowsDomainError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'    => 'Алиев',
                'firstName'   => 'Мухаммед',
                'profession'  => 'Электрик',
                'isForeigner' => true,
                'snils'       => '112-233-445 95',
                'citizenship' => 'Узбекистан',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Standard SNILS must be empty for a foreign worker.'], $data);
    }

    // -------------------------------------------------------------------------
    // Тест 5: Гражданин РФ без СНИЛС — ошибка валидации команды (422)
    // -------------------------------------------------------------------------

    public function testCitizenWithoutSnilsThrowsDomainError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'    => 'Иванов',
                'firstName'   => 'Иван',
                'profession'  => 'Слесарь',
                'isForeigner' => false,
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(
            ['errors' => ['snils' => 'SNILS is required for a citizen of Russia.']],
            $data,
        );
    }

    // -------------------------------------------------------------------------
    // Тест 6: Валидация — пустое ФИО (422)
    // -------------------------------------------------------------------------

    public function testEmptyNameReturnsValidationError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/workers',
            [
                'lastName'    => '',
                'firstName'   => '',
                'profession'  => 'Слесарь',
                'isForeigner' => false,
                'snils'       => '112-233-445 95',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Тест 7: Несуществующая компания — 409
    // -------------------------------------------------------------------------

    public function testCompanyNotFoundReturnsDomainError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/companies/dd8b1f8d-3cca-40f2-b21d-81c81cbf9579/workers',
            [
                'lastName'    => 'Иванов',
                'firstName'   => 'Иван',
                'profession'  => 'Слесарь',
                'isForeigner' => false,
                'snils'       => '112-233-445 95',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(['message' => 'Company is not found.'], $data);
    }
}
