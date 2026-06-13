<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class WorkerIdType extends StringType
{
    public const string NAME = 'worker_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof WorkerId ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WorkerId
    {
        return !empty($value) ? new WorkerId((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
