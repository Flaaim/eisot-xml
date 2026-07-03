<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Идентификатор работника (UUID).
 */
final class WorkerId
{
    public function __construct(
        private string $value
    ) {
        Assert::uuid($value);
        $this->value = mb_strtolower($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
