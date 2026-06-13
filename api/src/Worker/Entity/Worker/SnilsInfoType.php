<?php

declare(strict_types=1);

namespace App\Worker\Entity\Worker;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Хранит SnilsInfo как JSON:
 *   {"isForeigner":false,"snils":"644-670-185 07","citizenship":null,"foreignSnils":null}
 *   {"isForeigner":true,"snils":null,"citizenship":"Узбекистан","foreignSnils":"ABC123"}
 */
final class SnilsInfoType extends JsonType
{
    public const string NAME = 'worker_snils_info';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof SnilsInfo) {
            return parent::convertToDatabaseValue([
                'isForeigner'  => $value->isForeigner(),
                'snils'        => $value->getSnils()?->getValue(),
                'citizenship'  => $value->getCitizenship(),
                'foreignSnils' => $value->getForeignSnils(),
            ], $platform);
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SnilsInfo
    {
        $data = parent::convertToPHPValue($value, $platform);

        if ($data === null) {
            return null;
        }

        return SnilsInfo::fromPrimitives(
            (bool)$data['isForeigner'],
            $data['snils'] ?? null,
            $data['citizenship'] ?? null,
            $data['foreignSnils'] ?? null,
        );
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
