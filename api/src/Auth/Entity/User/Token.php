<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

final class Token
{
    public function __construct(
        private string $value {
            get {
                return $this->value;
            }
        },
        private DateTimeImmutable $expiresAt {
            get {
                return $this->expiresAt;
            }
        },
    ) {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }
}
