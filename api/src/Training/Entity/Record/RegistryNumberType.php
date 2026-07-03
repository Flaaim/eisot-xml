<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/** @psalm-suppress UnusedClass */
final class RegistryNumberType extends StringType
{
    public const string NAME = 'training_registry_number';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof RegistryNumber ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?RegistryNumber
    {
        return !empty($value) ? RegistryNumber::fromString((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
