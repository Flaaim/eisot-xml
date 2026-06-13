<?php

declare(strict_types=1);

namespace App\Company\MessageHandler;

use App\Company\Event\CompanyArchived;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnCompanyArchivedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CompanyArchived $event): void
    {
        $this->logger->info('Company archived', [
            'id' => $event->id->getValue(),
        ]);
    }
}
