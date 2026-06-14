<?php

declare(strict_types=1);

namespace App\Training\Command\RecordTrainingResult;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: зафиксировать результат обучения работника.
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $id,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $workerId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
        #[Assert\NotBlank]
        public readonly string $program,
        #[Assert\NotBlank]
        public readonly string $result,
        #[Assert\NotBlank]
        public readonly string $date,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public readonly string $protocolNumber,
    ) {}
}
