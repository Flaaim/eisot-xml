<?php

declare(strict_types=1);

namespace App\Worker\MessageHandler;

use App\Worker\Event\WorkerRegistered;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnWorkerRegisteredHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(WorkerRegistered $event): void
    {
        $this->logger->info('Worker registered', [
            'id'         => $event->id->getValue(),
            'company_id' => $event->companyId->getValue(),
            'full_name'  => $event->fullName->getFull(),
            'profession' => $event->profession->getValue(),
            'foreigner'  => $event->snilsInfo->isForeigner(),
        ]);
    }
}
