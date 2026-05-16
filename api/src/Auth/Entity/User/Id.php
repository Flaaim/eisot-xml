<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class Id
{
    public function __construct(
        private string $value {
            get {
                return $this->value;
            }
        }
    )
    {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}
