<?php

declare(strict_types=1);

namespace App\Company\Command\ChangeCompanyInn;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: изменить ИНН контрагента.
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
        #[Assert\Regex(pattern: '/^\d{10}(\d{2})?$/', message: 'INN must consist of 10 or 12 digits.')]
        public readonly string $inn,
    ) {}
}
