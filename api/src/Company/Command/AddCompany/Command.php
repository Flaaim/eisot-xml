<?php

declare(strict_types=1);

namespace App\Company\Command\AddCompany;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: добавить нового контрагента за текущим пользователем.
 *
 * Содержит только примитивные типы — DTO намерения пользователя.
 * Валидация инвариантов произойдёт при создании Value Objects в Handler.
 *
 * $userId заполняется контроллером из JWT-токена/сессии (не из тела запроса).
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $id,
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{10}(\d{2})?$/', message: 'INN must consist of 10 or 12 digits.')]
        public string $inn,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $userId,
    ) {}
}
