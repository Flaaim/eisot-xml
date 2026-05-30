<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class AuthUserAdapter implements PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly string $passwordHash
    ) {}

    public function getPassword(): string
    {
        return $this->passwordHash;
    }
}
