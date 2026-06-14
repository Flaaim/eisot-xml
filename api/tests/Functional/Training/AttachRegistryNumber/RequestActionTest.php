<?php

declare(strict_types=1);

namespace Tests\Functional\Training\AttachRegistryNumber;

use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Training\Event\RegistryNumberAttached;
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
    }

    // -------------------------------------------------------------------------
    // 200 — Happy Path
    // -------------------------------------------------------------------------

    public function testSuccessHappyPath(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->jsonRequest(
            'PATCH',
            '/v1/training-records/' . RequestFixture::RECORD_ID . '/registry-number',
            ['registryNumber' => 'РЕГ-001/2023'],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Номер сохранён в БД
        $record = $this->records->get(new Id(RequestFixture::RECORD_ID));
        self::assertNotNull($record->getRegistryNumber());
        self::assertEquals('РЕГ-001/2023', $record->getRegistryNumber()->getValue());

        // Событие
        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);
        self::assertInstanceOf(RegistryNumberAttached::class, $sent[0]->getMessage());
    }

    // -------------------------------------------------------------------------
    // 409 — уже прикреплён
    // -------------------------------------------------------------------------

    public function testAlreadyAttachedReturnsConflict(): void
    {
        // Первый раз — успех
        $this->client->jsonRequest(
            'PATCH',
            '/v1/training-records/' . RequestFixture::RECORD_ID . '/registry-number',
            ['registryNumber' => 'РЕГ-001/2023'],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Второй раз — конфликт
        $this->client->jsonRequest(
            'PATCH',
            '/v1/training-records/' . RequestFixture::RECORD_ID . '/registry-number',
            ['registryNumber' => 'РЕГ-002/2023'],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        $data = Json::decode($this->client->getResponse()->getContent());
        self::assertEquals(['message' => 'Registry number is already attached.'], $data);
    }

    // -------------------------------------------------------------------------
    // 401 — без авторизации
    // -------------------------------------------------------------------------

    public function testUnauthorized(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/training-records/' . RequestFixture::RECORD_ID . '/registry-number',
            ['registryNumber' => 'РЕГ-001/2023'],
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 409 — record not found
    // -------------------------------------------------------------------------

    public function testRecordNotFoundReturnsDomainError(): void
    {
        $this->client->jsonRequest(
            'PATCH',
            '/v1/training-records/dd8b1f8d-3cca-40f2-b21d-81c81cbf9579/registry-number',
            ['registryNumber' => 'РЕГ-001/2023'],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
    }
}
