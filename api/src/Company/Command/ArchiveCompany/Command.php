<?php

declare(strict_types=1);

namespace App\Company\Command\ArchiveCompany;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: перевести компанию в архив (мягкое удаление).
 *
 * Содержит только идентификатор компании — DTO намерения пользователя.
 * Бизнес-семантика: «Архивировать», а не «Удалить».
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $id,
    ) {}
}
