<?php

declare(strict_types=1);

namespace App\Company\Entity\Company;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine Type для UserId.
 *
 * Сохраняет UUID пользователя как строку VARCHAR(36) в БД,
 * преобразует обратно в UserId при чтении.
 */
final class UserIdType extends StringType
{
    public const string NAME = 'company_user_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof UserId ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        return !empty($value) ? new UserId((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
