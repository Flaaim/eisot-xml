<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\ActivateTrialSubscription;

use App\Auth\Entity\User\Id as AuthUserId;
use App\Auth\Entity\User\UserRepository;
use App\Subscription\Entity\Subscription\Plan;
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
    private UserRepository $users;
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

        $this->users = $container->get(UserRepository::class);

        $this->accessToken = $this->getAccessToken(
            $this->client,
            RequestFixture::USER_EMAIL,
            RequestFixture::USER_PASS,
        );
    }

    public function testActivateTrialSuccess(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/trial/activate',
            [],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $subscription = $this->subscriptions->findActiveByUserId(
            new UserId(RequestFixture::USER_ID),
        );
        self::assertNotNull($subscription);
        self::assertSame(Plan::TRIAL, $subscription->getPlan());
        self::assertSame(3, $subscription->getPeriod()->getDurationDays());

        $user = $this->users->get(new AuthUserId(RequestFixture::USER_ID));
        self::assertTrue($user->isTrialUsed());
    }

    public function testActivateTrialTwiceReturnsConflict(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/trial/activate',
            [],
            $this->authHeaders($this->accessToken),
        );
        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/trial/activate',
            [],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());
        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertSame('Trial Subscription has already been used.', $data['message']);
    }

    public function testCheckAccessReturnsTrialFlags(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/v1/user/subscription/access',
            [],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = Json::decode((string)$this->client->getResponse()->getContent());

        self::assertFalse($data['hasAccess']);
        self::assertFalse($data['trialUsed']);
        self::assertTrue($data['trialAvailable']);
    }
}
