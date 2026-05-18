<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Entity\User\Email;
use App\Auth\Event\PasswordResetRequested;
use App\Auth\Service\PasswordResetTokenSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendTokenOnResetPasswordHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PasswordResetTokenSender $sender,
    ) {}

    public function __invoke(PasswordResetRequested $event): void
    {
        $this->logger->info('User ' . $event->email . ' requested reset password token');

        $email = new Email($event->email);

        $this->sender->send($email, $event->token);
    }
}
