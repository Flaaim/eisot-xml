<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Хранит FullName как JSON: {"last":"...","first":"...","middle":"..."|null}.
 *
 * @psalm-suppress UnusedClass
 */
final class FullNameType extends JsonType
{
    public const string NAME = 'worker_full_name';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof FullName) {
            return parent::convertToDatabaseValue([
                'last'   => $value->getLastName(),
                'first'  => $value->getFirstName(),
                'middle' => $value->getMiddleName(),
            ], $platform);
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?FullName
    {
        $data = parent::convertToPHPValue($value, $platform);

        if (null === $data) {
            return null;
        }

        return FullName::create(
            $data['last'],
            $data['first'],
            $data['middle'] ?? null,
        );
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
