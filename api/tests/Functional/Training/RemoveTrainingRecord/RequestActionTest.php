<?php

declare(strict_types=1);

namespace Tests\Functional\Training\RemoveTrainingRecord;

use App\Training\Entity\Record\Id;
use App\Training\Entity\Record\TrainingRecordRepository;
use App\Worker\Entity\Worker\WorkerId;
use App\Worker\Entity\Worker\WorkerRepository;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
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
    private string $ownerToken;
    private string $otherToken;

    private TrainingRecordRepository $records;
    private WorkerRepository $workers;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container    = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);
        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->records = new TrainingRecordRepository($em);
        $this->workers = new WorkerRepository($em);
    }

    public function testUnauthorized(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/' . RequestFixture::RECORD_ID_ONE,
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testForbiddenForNonOwner(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/' . RequestFixture::RECORD_ID_ONE,
            [],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccessHappyPath(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/' . RequestFixture::RECORD_ID_ONE,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        self::assertNull($this->records->find(new Id(RequestFixture::RECORD_ID_ONE)));
    }

    public function testSuccessDeleteAllRecords(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/' . RequestFixture::RECORD_ID_ONE,
            [],
            $this->authHeaders($this->ownerToken),
        );

        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/' . RequestFixture::RECORD_ID_TWO,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        self::assertNull($this->records->find(new Id(RequestFixture::RECORD_ID_ONE)));
        self::assertNull($this->records->find(new Id(RequestFixture::RECORD_ID_TWO)));

        self::expectException(DomainException::class);
        $this->workers->get(new WorkerId(RequestFixture::WORKER_ID));
    }

    public function testCompanyNotFound(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/a8953006-83bd-4878-860a-fed65afc4b44/' . RequestFixture::RECORD_ID_ONE,
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Company is not found.'], $data);
    }

    public function testRecordNotFound(): void
    {
        $this->client->jsonRequest(
            'DELETE',
            '/v1/companies/' . RequestFixture::COMPANY_ID . '/a8953006-83bd-4878-860a-fed65afc4b44',
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());

        $data = Json::decode($body);

        self::assertEquals(['message' => 'Record not found.'], $data);
    }
}
