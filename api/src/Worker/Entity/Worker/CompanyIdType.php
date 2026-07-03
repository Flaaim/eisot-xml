<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/** @psalm-suppress UnusedClass */
final class CompanyIdType extends StringType
{
    public const string NAME = 'worker_company_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof CompanyId ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CompanyId
    {
        return !empty($value) ? new CompanyId((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
