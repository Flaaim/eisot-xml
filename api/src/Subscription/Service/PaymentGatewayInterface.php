<?php

declare(strict_types=1);

namespace App\Subscription\Service;

use App\Subscription\Entity\Payment\Amount;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;

interface PaymentGatewayInterface
{
    /**
     * @throws PaymentGatewayException
     */
    public function createPayment(
        UserId $userId,
        Plan $plan,
        Amount $amount,
        int $durationDays,
        string $returnUrl,
    ): PaymentGatewayResult;
}
