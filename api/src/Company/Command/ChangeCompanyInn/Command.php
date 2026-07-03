<?php

declare(strict_types=1);

namespace App\Company\Command\ChangeCompanyInn;

use App\Company\Entity\Company\Inn;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Команда: изменить ИНН контрагента.
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
        public readonly string $id,
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{10}(\d{2})?$/', message: 'INN must consist of 10 or 12 digits.')]
        public readonly string $inn,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
    ) {}

    #[Assert\Callback]
    public function validateInnChecksum(ExecutionContextInterface $context): void
    {
        if ('' === $this->inn || !preg_match('/^\d{10}(\d{2})?$/', $this->inn)) {
            return;
        }

        try {
            Inn::fromString($this->inn);
        } catch (InvalidArgumentException $exception) {
            $context->buildViolation($exception->getMessage())
                ->atPath('inn')
                ->addViolation();
        }
    }
}
