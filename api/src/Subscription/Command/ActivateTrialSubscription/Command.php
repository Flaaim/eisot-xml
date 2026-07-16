<?php

declare(strict_types=1);

namespace App\Subscription\Command\ActivateTrialSubscription;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Команда: активировать Trial Subscription на 3 дня (один раз за историю User).
 */
final readonly class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $userId,
    ) {}
}
