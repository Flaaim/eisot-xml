<?php

declare(strict_types=1);

namespace App\Subscription\MessageHandler;

use App\Infrastructure\Doctrine\Flusher;
use App\Subscription\Command\ActivateSubscription\Command as ActivateSubscriptionCommand;
use App\Subscription\Command\ActivateSubscription\Handler as ActivateSubscriptionHandler;
use App\Subscription\Entity\Subscription\SubscriptionRepository;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Event\PaymentConfirmed;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final readonly class OnPaymentConfirmedHandler
{
    public function __construct(
        private SubscriptionRepository $subscriptions,
        private ActivateSubscriptionHandler $activateSubscriptionHandler,
        private Flusher $flusher,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(PaymentConfirmed $event): void
    {
        $activeSubscription = $this->subscriptions->findActiveByUserId(new UserId($event->userId));

        if (null !== $activeSubscription) {
            $activeSubscription->extend($event->durationDays);
            $this->flusher->flush();

            $this->logger->info('User Subscription extended after payment confirmation.', [
                'paymentId' => $event->paymentId,
                'userId' => $event->userId,
                'plan' => $event->plan,
                'durationDays' => $event->durationDays,
            ]);

            return;
        }

        $this->activateSubscriptionHandler->handle(new ActivateSubscriptionCommand(
            planId: $event->plan,
            durationDays: $event->durationDays,
            userId: $event->userId,
        ));

        $this->logger->info('User Subscription activated after payment confirmation.', [
            'paymentId' => $event->paymentId,
            'userId' => $event->userId,
            'plan' => $event->plan,
            'durationDays' => $event->durationDays,
        ]);
    }
}
