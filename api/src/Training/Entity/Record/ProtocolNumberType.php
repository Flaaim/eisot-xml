<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProtocolNumberType extends StringType
{
    public const string NAME = 'training_protocol_number';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof ProtocolNumber ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProtocolNumber
    {
        return !empty($value) ? ProtocolNumber::fromString((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
