<?php

declare(strict_types=1);

namespace App\Subscription\MessageHandler;

use App\Subscription\Event\SubscriptionExpired;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final readonly class LogOnSubscriptionExpiredHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SubscriptionExpired $event): void
    {
        $this->logger->info('User Subscription expired.', [
            'subscriptionId' => $event->id->getValue(),
            'userId' => $event->userId->getValue(),
        ]);
    }
}
