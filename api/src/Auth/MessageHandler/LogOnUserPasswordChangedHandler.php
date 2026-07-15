<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\PasswordChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class LogOnUserPasswordChangedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(PasswordChanged $event): void
    {
        $userId = $event->id;

        $this->logger->info('User password changed ' . $userId);
    }
}
