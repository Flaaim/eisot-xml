<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\CreatePayment;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;
use Tests\Functional\OAuthTokenTrait;
use Tests\Functional\Subscription\ActivateSubscription\RequestFixture;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    use OAuthTokenTrait;

    private KernelBrowser $client;
    private string $accessToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        $this->accessToken = $this->getAccessToken(
            $this->client,
            RequestFixture::USER_EMAIL,
            RequestFixture::USER_PASS,
        );
    }

    public function testCreatePaymentReturnsConfirmationUrl(): void
    {
        $this->client->catchExceptions(false);
        $this->client->jsonRequest(
            'POST',
            '/v1/user/subscription/payment',
            [
                'planId' => 'basic',
                'durationDays' => 30,
                'returnUrl' => 'http://localhost:3000/user/subscription/callback',
            ],
            $this->authHeaders($this->accessToken),
        );

        self::assertEquals(201, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);
        self::assertArrayHasKey('paymentId', $data);
        self::assertArrayHasKey('confirmationUrl', $data);
        self::assertStringContainsString('/user/subscription/callback', $data['confirmationUrl']);
    }
}
