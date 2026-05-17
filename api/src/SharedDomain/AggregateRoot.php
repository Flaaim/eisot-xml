<?php

declare(strict_types=1);

namespace App\SharedDomain;

interface AggregateRoot
{
    public function releaseEvents();
}
