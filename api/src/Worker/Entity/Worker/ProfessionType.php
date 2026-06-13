<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProfessionType extends StringType
{
    public const string NAME = 'worker_profession';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Profession ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Profession
    {
        return !empty($value) ? Profession::fromString((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
