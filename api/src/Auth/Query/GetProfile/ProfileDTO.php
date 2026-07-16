<?php

declare(strict_types=1);

namespace App\Auth\Query\GetProfile;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class ProfileDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $role,
    ) {}
}
