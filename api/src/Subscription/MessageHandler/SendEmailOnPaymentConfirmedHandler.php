<?php

declare(strict_types=1);

namespace App\Subscription\MessageHandler;

use App\Auth\ReadModel\UserFetcherInterface;
use App\Subscription\Event\PaymentConfirmed;
use App\Subscription\Service\PaymentConfirmedSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class SendEmailOnPaymentConfirmedHandler
{
    public function __construct(
        private readonly PaymentConfirmedSender $sender,
        private readonly UserFetcherInterface $userFetcher,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(PaymentConfirmed $event): void
    {
        $userId = $event->userId;
        $user = $this->userFetcher->findDetail($userId);

        if (null === $user || empty($user['email'])) {
            $this->logger->error('User not found', ['id' => $userId]);
            return;
        }

        $this->sender->send($user['email'], $event->durationDays);
    }
}
