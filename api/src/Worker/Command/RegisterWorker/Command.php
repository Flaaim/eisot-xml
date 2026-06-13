<?php

declare(strict_types=1);

namespace App\Worker\Command\RegisterWorker;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: зарегистрировать работника в компании.
 *
 * $userId и $companyId заполняются контроллером (JWT + route param).
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $workerId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $companyId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public readonly string $lastName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public readonly string $firstName,
        #[Assert\Length(max: 100)]
        public readonly ?string $middleName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 200)]
        public readonly string $profession,
        public readonly bool $isForeigner,
        public readonly ?string $snils,
        public readonly ?string $citizenship,
        public readonly ?string $foreignSnils,
    ) {}
}
