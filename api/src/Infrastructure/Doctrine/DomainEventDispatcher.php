<?php

declare(strict_types=1);

namespace Infrastructure\Doctrine;

final class DomainEventDispatcher
{
    public function onFlush(): void {}

    public function postFlush(): void {}
}
