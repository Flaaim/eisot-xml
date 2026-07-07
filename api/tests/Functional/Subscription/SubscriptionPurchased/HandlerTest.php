<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\SubscriptionPurchased;

use App\Subscription\Event\SubscriptionPurchased;
use App\Subscription\MessageHandler\SendEmailOnSubscriptionPurchasedHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Functional\FixturesLoader;

/**
 * @internal
 * @coversNothing
 */
final class HandlerTest extends KernelTestCase
{
    public function testSendEmailToUserWhenSubscriptionPurchased(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $fixture = new FixturesLoader($container);
        $fixture->loadFixtures([RequestFixture::class]);

        $handler = $container->get(SendEmailOnSubscriptionPurchasedHandler::class);
        $message = new SubscriptionPurchased(
            '0000110',
            RequestFixture::USER_ID,
            'basic',
            '07.08.2026'
        );

        $handler($message);
        self::assertEmailCount(1);
    }
}
