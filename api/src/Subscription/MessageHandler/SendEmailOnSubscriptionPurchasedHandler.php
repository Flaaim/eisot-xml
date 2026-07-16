<?php

declare(strict_types=1);

namespace App\Subscription\MessageHandler;

use App\Auth\ReadModel\UserFetcherInterface;
use App\Subscription\Event\SubscriptionPurchased;
use App\Subscription\Service\PaymentConfirmedSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class SendEmailOnSubscriptionPurchasedHandler
{
    public function __construct(
        private readonly PaymentConfirmedSender $sender,
        private readonly UserFetcherInterface $userFetcher,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(SubscriptionPurchased $event): void
    {
        $userId = $event->userId;
        $user = $this->userFetcher->findDetail($userId);

        if (null === $user || '' === $user['email']) {
            $this->logger->error('User not found', ['id' => $userId]);
            return;
        }

        $this->sender->send($user['email'], $event->ended);
    }
}
