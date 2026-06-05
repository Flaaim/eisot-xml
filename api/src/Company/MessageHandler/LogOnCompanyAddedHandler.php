<?php

declare(strict_types=1);

namespace App\Company\MessageHandler;

use App\Company\Entity\Company\Event\CompanyAdded;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnCompanyAddedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CompanyAdded $event): void
    {
        $this->logger->info('Company added', [
            'id'   => $event->id->getValue(),
            'name' => $event->name->getValue(),
            'inn'  => $event->inn->getValue(),
        ]);
    }
}
