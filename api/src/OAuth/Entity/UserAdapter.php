<?php

declare(strict_types=1);

namespace App\OAuth\Entity;



use Symfony\Component\Security\Core\User\UserInterface;

final class UserAdapter implements UserInterface
{
    public function __construct(
        private readonly string $identifier,
    )
    {}
    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
