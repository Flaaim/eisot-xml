<?php

declare(strict_types=1);

namespace App\Auth\Entity\User;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class RoleType extends StringType
{
    const NAME = 'user_role';
}
