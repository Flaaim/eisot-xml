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
        #[Assert\When(
            expression: 'this.isForeigner === false',
            constraints: [
                new Assert\NotBlank(message: 'SNILS is required for a citizen of Russia.'),
                new Assert\Regex(
                    pattern: '/^\d{3}-\d{3}-\d{3} \d{2}$/',
                    message: 'SNILS must match format XXX-XXX-XXX XX.',
                ),
            ],
        )]
        public readonly ?string $snils,
        #[Assert\When(
            expression: 'this.isForeigner === true',
            constraints: [
                new Assert\NotBlank(message: 'Citizenship is required for a foreign worker.'),
                new Assert\Length(max: 100),
            ],
        )]
        public readonly ?string $citizenship,
        #[Assert\When(
            expression: 'this.isForeigner === true and this.foreignSnils !== null and this.foreignSnils !== ""',
            constraints: [
                new Assert\Length(max: 30),
            ],
        )]
        public readonly ?string $foreignSnils,
    ) {}
}
