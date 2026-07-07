<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Payment;

use Webmozart\Assert\Assert;

final readonly class Amount
{
    public function __construct(
        private string $value,
        private string $currency = 'RUB',
    ) {
        Assert::regex($value, '/^\d+\.\d{2}$/', 'Amount must have two decimal places, e.g. 490.00');
        Assert::lengthBetween($currency, 3, 3);
    }

    public static function fromRubles(string $value): self
    {
        return new self($value, 'RUB');
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value && $this->currency === $other->currency;
    }
}
