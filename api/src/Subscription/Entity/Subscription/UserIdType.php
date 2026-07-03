<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Subscription;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/** @psalm-suppress UnusedClass */
final class UserIdType extends StringType
{
    public const string NAME = 'subscription_user_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof UserId ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        return !empty($value) ? new UserId((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
