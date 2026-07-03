<?php

declare(strict_types=1);

namespace App\Auth\MessageHandler;

use App\Auth\Event\UserCreated;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class LogOnUserCreatedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(UserCreated $event): void
    {
        $email = $event->email;

        $this->logger->info('User created', ['email' => $email]);
    }
}
