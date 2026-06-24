<?php

declare(strict_types=1);

namespace App\Company\Command\RestoreCompany;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: восстановить компанию из архива.
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $id,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
    ) {}
}
