<?php

declare(strict_types=1);

namespace App\Company\MessageHandler;

use App\Company\Event\CompanyInnChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnCompanyInnChangedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CompanyInnChanged $event): void
    {
        $this->logger->info('Company INN changed', [
            'id'  => $event->id->getValue(),
            'inn' => $event->inn->getValue(),
        ]);
    }
}
