<?php

declare(strict_types=1);

namespace App\Training\Entity\Record;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ProgramType extends StringType
{
    public const string NAME = 'training_program';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof Program ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Program
    {
        return !empty($value) ? Program::fromString((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
