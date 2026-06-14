<?php

declare(strict_types=1);

namespace App\Training\Command\AttachRegistryNumber;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: прикрепить регистрационный номер из реестра Минтруда.
 */
final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $recordId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public readonly string $registryNumber,
    ) {}
}
