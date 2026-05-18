<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class StatusType extends StringType
{
    const NAME = 'user_status';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Status ? $value->getValue() : $value;
    }
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Status
    {
        return !empty($value) ? new Status((string)$value) : null;
    }
}
