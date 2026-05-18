<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\PasswordReset;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnUserPasswordResetHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(PasswordReset $event): void
    {
        $userId = $event->id;

        $this->logger->info('User password reset' . $userId);
    }
}
