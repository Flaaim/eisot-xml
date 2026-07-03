<?php

declare(strict_types=1);

namespace App\Subscription\Command\ActivateSubscription;

use Symfony\Component\Validator\Constraints as Assert;

final class Command
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['basic', 'premium'], message: 'Plan must be basic or premium.')]
        public readonly string $planId,
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $durationDays,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
    ) {}
}
