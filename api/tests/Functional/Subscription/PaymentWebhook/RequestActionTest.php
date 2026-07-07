<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\PaymentWebhook;

use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\PaymentRepository;
use App\Subscription\Entity\Payment\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    private KernelBrowser $client;
    private PaymentRepository $payments;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->payments = new PaymentRepository($em);
    }

    public function testPaymentSucceededWebhookConfirmsPayment(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/subscription/payment/webhook',
            [
                'event' => 'payment.succeeded',
                'object' => [
                    'id' => RequestFixture::EXTERNAL_ID,
                    'status' => 'succeeded',
                ],
            ],
        );

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $payment = $this->payments->findByExternalId(new ExternalId(RequestFixture::EXTERNAL_ID));
        self::assertNotNull($payment);
        self::assertSame(PaymentStatus::SUCCEEDED, $payment->getStatus());
    }

    public function testPaymentCanceledWebhookMarksPaymentFailed(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/v1/subscription/payment/webhook',
            [
                'event' => 'payment.canceled',
                'object' => [
                    'id' => RequestFixture::EXTERNAL_ID,
                    'status' => 'canceled',
                ],
            ],
        );

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $payment = $this->payments->findByExternalId(new ExternalId(RequestFixture::EXTERNAL_ID));
        self::assertNotNull($payment);
        self::assertSame(PaymentStatus::FAILED, $payment->getStatus());
    }
}
