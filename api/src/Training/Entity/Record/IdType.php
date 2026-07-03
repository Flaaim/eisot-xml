<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/** @psalm-suppress UnusedClass */
final class IdType extends StringType
{
    public const string NAME = 'training_record_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Id ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        return !empty($value) ? new Id((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
