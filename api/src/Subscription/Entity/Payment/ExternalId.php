<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Payment;

use Webmozart\Assert\Assert;

final class ExternalId
{
    public function __construct(
        private string $value,
    ) {
        Assert::notEmpty($value);
        Assert::maxLength($value, 64);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
