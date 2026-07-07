<?php

declare(strict_types=1);

namespace App\Subscription\Query\GetPaymentStatus;

use Symfony\Component\Validator\Constraints as Assert;

final class Query
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $paymentId,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $userId,
    ) {}
}
