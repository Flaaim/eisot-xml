<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\ActivateSubscription;

use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
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
    private SubscriptionRepository $subscriptions;
    private string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->subscriptions = new SubscriptionRepository($em);

        $this->accessToken = $this->getAccessToken(
            $this->client,
            RequestFixture::USER_EMAIL,
            RequestFixture::USER_PASS,
        );
    }

    public function testActivateUserSubscriptionSuccess(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/activate',
            ['planId' => 'basic', 'durationDays' => 30],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('id', $data);

        $subscription = $this->subscriptions->findActiveByUserId(
            new UserId(RequestFixture::USER_ID),
        );
        self::assertNotNull($subscription);
        self::assertTrue($subscription->isActive());
    }

    public function testDuplicateActiveUserSubscriptionReturnsConflict(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/activate',
            ['planId' => 'basic', 'durationDays' => 30],
            $this->authHeaders($this->accessToken),
        );

        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/activate',
            ['planId' => 'premium', 'durationDays' => 30],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
    }
}
