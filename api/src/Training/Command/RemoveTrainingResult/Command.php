<?php

declare(strict_types=1);

namespace App\Training\Command\RemoveTrainingResult;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $id,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $companyId,
    ) {}
}
