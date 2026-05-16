<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Webmozart\Assert\Assert;

final class Email
{
    public function __construct(
        public string $value {
            get {
                return $this->value;
            }
        }
    )
    {
        Assert::notEmpty($value);
        Assert::email($value);
        $this->value = mb_strtolower($value);
    }
}
