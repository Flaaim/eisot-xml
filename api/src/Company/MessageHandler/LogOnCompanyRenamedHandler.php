<?php

declare(strict_types=1);

namespace App\Company\MessageHandler;

use App\Company\Event\CompanyRenamed;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** @psalm-suppress UnusedClass */
#[AsMessageHandler]
final class LogOnCompanyRenamedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CompanyRenamed $event): void
    {
        $this->logger->info('Company renamed', [
            'id'   => $event->id->getValue(),
            'name' => $event->name->getValue(),
        ]);
    }
}
