<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\OnPaymentConfirmed;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Command\ActivateSubscription\Handler as ActivateSubscriptionHandler;
use App\Subscription\Entity\Payment\Id as PaymentId;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Event\PaymentConfirmed;
use App\Subscription\MessageHandler\OnPaymentConfirmedHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Subscription\PaymentWebhook\RequestFixture;

/**
 * @internal
 * @coversNothing
 */
final class HandlerTest extends KernelTestCase
{
    public function testActivatesSubscriptionWhenUserHasNoActivePlan(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $fixturesLoader = new FixturesLoader($container);
        $fixturesLoader->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $handler = new OnPaymentConfirmedHandler(
            new SubscriptionRepository($em),
            $container->get(ActivateSubscriptionHandler::class),
            $container->get(Flusher::class),
            new NullLogger(),
        );

        $userId = new UserId(RequestFixture::USER_ID);

        $handler(new PaymentConfirmed(
            new PaymentId(RequestFixture::PAYMENT_ID),
            $userId,
            Plan::BASIC,
            30,
        ));

        $subscription = (new SubscriptionRepository($em))->findActiveByUserId($userId);
        self::assertNotNull($subscription);
        self::assertTrue($subscription->isActive());
    }
}
