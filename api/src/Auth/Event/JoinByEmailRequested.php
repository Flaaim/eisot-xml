<?php

declare(strict_types=1);

namespace App\Auth\Event;

final class JoinByEmailRequested
{
    public function __construct(
        public string $token,
        public string $email
    ) {}
}
