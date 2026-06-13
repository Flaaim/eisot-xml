<?php

declare(strict_types=1);

namespace App\Company\Command\RenameCompany;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: переименовать контрагента.
 *
 * Содержит только примитивные типы — DTO намерения пользователя.
 * Валидация инвариантов произойдёт при создании Value Objects в Handler.
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $id,
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        public readonly string $name,
    ) {}
}
