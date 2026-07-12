<?php

declare(strict_types=1);

namespace App\Company\MessageHandler;

use App\Company\Event\CompanyRemoved;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class LogOnCompanyRemovedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CompanyRemoved $event): void
    {
        $this->logger->info('Company permanently removed', [
            'id' => $event->id->getValue(),
        ]);
    }
}
