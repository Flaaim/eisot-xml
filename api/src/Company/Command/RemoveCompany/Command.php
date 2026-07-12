<?php

declare(strict_types=1);

namespace App\Company\Command\RemoveCompany;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: безвозвратно удалить архивированную компанию.
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
