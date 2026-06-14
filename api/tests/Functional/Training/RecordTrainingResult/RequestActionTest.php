<?php

declare(strict_types=1);

namespace Tests\Functional\Training\RecordTrainingResult;

use App\Training\Entity\Record\TrainingRecordRepository;
use App\Training\Event\TrainingResultRecorded;
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
    private TrainingRecordRepository $records;
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
        $this->records = new TrainingRecordRepository($em);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);
        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);
    }

    // -------------------------------------------------------------------------
    // 401 — без авторизации
    // -------------------------------------------------------------------------

    public function testUnauthorized(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => '1. Оказание первой помощи пострадавшим',
                'result'         => 'удовлетворительно',
                'date'           => '28.09.2023 16:56:01',
                'protocolNumber' => 'ПР-001',
            ],
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 403 — чужой пользователь
    // -------------------------------------------------------------------------

    public function testForbiddenForNonOwner(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => '1. Оказание первой помощи пострадавшим',
                'result'         => 'удовлетворительно',
                'date'           => '28.09.2023 16:56:01',
                'protocolNumber' => 'ПР-001',
            ],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 201 — Happy Path
    // -------------------------------------------------------------------------

    public function testSuccessHappyPath(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => '1. Оказание первой помощи пострадавшим',
                'result'         => 'удовлетворительно',
                'date'           => '28.09.2023 16:56:01',
                'protocolNumber' => 'ПР-001/2023',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('id', $data);

        // Запись сохранена в БД
        $record = $this->records->get(new \App\Training\Entity\Record\Id($data['id']));
        self::assertEquals('1. Оказание первой помощи пострадавшим', $record->getProgram()->getValue());
        self::assertTrue($record->getResult()->isSatisfactory());
        self::assertEquals('28.09.2023 16:56:01', $record->getDate()->format('d.m.Y H:i:s'));
        self::assertEquals('ПР-001/2023', $record->getProtocolNumber()->getValue());
        self::assertNull($record->getRegistryNumber());

        // Событие
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);
        self::assertInstanceOf(TrainingResultRecorded::class, $sent[0]->getMessage());
    }

    // -------------------------------------------------------------------------
    // 201 — неудовлетворительный результат
    // -------------------------------------------------------------------------

    public function testSuccessUnsatisfactory(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => '9. Безопасные методы и приемы выполнения работ на высоте',
                'result'         => 'неудовлетворительно',
                'date'           => '01.10.2023 10:00:00',
                'protocolNumber' => 'ПР-002/2023',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $data   = Json::decode($this->client->getResponse()->getContent());
        $record = $this->records->get(new \App\Training\Entity\Record\Id($data['id']));
        self::assertFalse($record->getResult()->isSatisfactory());
    }

    // -------------------------------------------------------------------------
    // 409 — недопустимая программа (DomainException через InvalidArgument)
    // -------------------------------------------------------------------------

    public function testInvalidProgramThrowsDomainError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => 'Несуществующая программа',
                'result'         => 'удовлетворительно',
                'date'           => '28.09.2023 16:56:01',
                'protocolNumber' => 'ПР-001',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 409 — worker not found
    // -------------------------------------------------------------------------

    public function testWorkerNotFoundReturnsDomainError(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/dd8b1f8d-3cca-40f2-b21d-81c81cbf9579/training-records',
            [
                'program'        => '1. Оказание первой помощи пострадавшим',
                'result'         => 'удовлетворительно',
                'date'           => '28.09.2023 16:56:01',
                'protocolNumber' => 'ПР-001',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 422 — валидация (пустые обязательные поля)
    // -------------------------------------------------------------------------

    public function testValidationEmptyFields(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/workers/' . RequestFixture::WORKER_ID . '/training-records',
            [
                'program'        => '',
                'result'         => '',
                'date'           => '',
                'protocolNumber' => '',
            ],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(422, $this->client->getResponse()->getStatusCode());
    }
}
