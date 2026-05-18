<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
final class EmailType extends StringType
{
    public const NAME = 'user_email';
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Email ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        return !empty($value) ? new Email((string)$value) : null;
    }
    public function getName(): string
    {
        return self::NAME;
    }
}
