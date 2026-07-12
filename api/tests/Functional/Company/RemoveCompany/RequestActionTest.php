<?php

declare(strict_types=1);

namespace Tests\Functional\Company\RemoveCompany;

use App\Company\Entity\Company\CompanyRepository;
use App\Company\Event\CompanyRemoved;
use Doctrine\DBAL\Connection;
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
    private Connection $connection;
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
        $this->companies  = new CompanyRepository($em);
        $this->connection = $container->get(Connection::class);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::USER_EMAIL, RequestFixture::USER_PASS);
        $this->otherToken = $this->getAccessToken($this->client, RequestFixture::OTHER_USER_EMAIL, RequestFixture::USER_PASS);
    }

    public function testUnauthenticatedReturns401(): void
    {
        $this->client->request(
            'DELETE',
            '/v1/companies/' . RequestFixture::ARCHIVED_COMPANY_ID,
        );

        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testForbiddenForNonOwner(): void
    {
        $this->client->request(
            'DELETE',
            '/v1/companies/' . RequestFixture::ARCHIVED_COMPANY_ID,
            [],
            [],
            $this->authHeaders($this->otherToken),
        );

        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSuccessRemovesCompanyAndRelatedData(): void
    {
        $this->client->getContainer()->get('messenger.transport.async')->reset();

        $this->client->request(
            'DELETE',
            '/v1/companies/' . RequestFixture::ARCHIVED_COMPANY_ID,
            [],
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $companyCount = (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM companies WHERE id = :id',
            ['id' => RequestFixture::ARCHIVED_COMPANY_ID],
        );
        self::assertSame(0, $companyCount);

        $workersCount = (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM workers WHERE company_id = :companyId',
            ['companyId' => RequestFixture::ARCHIVED_COMPANY_ID],
        );
        self::assertSame(0, $workersCount);

        $recordsCount = (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM training_records WHERE worker_id = :workerId',
            ['workerId' => RequestFixture::WORKER_ID],
        );
        self::assertSame(0, $recordsCount);

        $transport = $this->client->getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);

        $event = $sent[0]->getMessage();
        self::assertInstanceOf(CompanyRemoved::class, $event);
        self::assertEquals(RequestFixture::ARCHIVED_COMPANY_ID, $event->id->getValue());
    }

    public function testActiveCompanyCannotBeRemoved(): void
    {
        $this->client->request(
            'DELETE',
            '/v1/companies/' . RequestFixture::ACTIVE_COMPANY_ID,
            [],
            [],
            $this->authHeaders($this->ownerToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertEquals(
            ['message' => 'Only archived companies can be permanently removed.'],
            $data,
        );
    }
}
