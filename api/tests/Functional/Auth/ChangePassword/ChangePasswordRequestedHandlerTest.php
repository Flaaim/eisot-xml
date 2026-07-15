<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use App\Auth\Event\PasswordChanged;
use App\Auth\MessageHandler\SendInfoOnChangedPasswordHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
final class ChangePasswordRequestedHandlerTest extends KernelTestCase
{
    public function testSuccess(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $handler = $container->get(SendInfoOnChangedPasswordHandler::class);
        $message = new PasswordChanged(Uuid::uuid4()->toString(), 'test@email.com');

        $handler($message);

        self::assertEmailCount(1);
        self::assertEmailAddressContains(self::getMailerMessage(0), 'To', 'test@email.com');
    }
}
