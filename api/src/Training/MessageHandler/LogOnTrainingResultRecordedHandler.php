<?php

declare(strict_types=1);

namespace App\Training\MessageHandler;

use App\Training\Event\TrainingResultRecorded;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogOnTrainingResultRecordedHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(TrainingResultRecorded $event): void
    {
        $this->logger->info('Training result recorded', [
            'id'              => $event->id->getValue(),
            'worker_id'       => $event->workerId->getValue(),
            'program'         => $event->program->getValue(),
            'result'          => $event->result->getValue(),
            'date'            => $event->date->format('d.m.Y H:i:s'),
            'protocol_number' => $event->protocolNumber->getValue(),
        ]);
    }
}
