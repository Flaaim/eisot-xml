<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Payment;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/** @psalm-suppress UnusedClass */
final class IdType extends StringType
{
    public const string NAME = 'subscription_payment_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Id ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return new Id((string)$value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
