<?php

declare(strict_types=1);

namespace App\Subscription\MessageHandler;

use App\Subscription\Event\SubscriptionPurchased;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final readonly class LogOnSubscriptionPurchasedHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(SubscriptionPurchased $event): void
    {
        $this->logger->info('User Subscription purchased.', [
            'subscriptionId' => $event->id,
            'userId' => $event->userId,
            'plan' => $event->plan,
            'end' => $event->ended,
        ]);
    }
}
