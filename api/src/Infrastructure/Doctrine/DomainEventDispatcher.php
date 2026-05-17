<?php

declare(strict_types=1);

namespace Infrastructure\Doctrine;

use App\SharedDomain\AggregateRoot;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

final class DomainEventDispatcher
{
    private array $events = [];

    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        $entities = [
            ...$uow->getScheduledEntityInsertions(),
            ...$uow->getScheduledEntityUpdates(),
            ...$uow->getScheduledEntityDeletions(),
        ];

        foreach ($entities as $entity) {
            if ($entity instanceof AggregateRoot) {
                foreach ($entity->releaseEvents() as $event) {
                    $this->events[] = $event;
                }
            }
        }
    }

    public function postFlush(): void
    {
        $events = $this->events;
        $this->events = [];
        foreach ($events as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
