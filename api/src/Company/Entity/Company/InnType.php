<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class InnType extends StringType
{
    public const string NAME = 'company_inn';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Inn ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Inn
    {
        return !empty($value) ? new Inn((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
