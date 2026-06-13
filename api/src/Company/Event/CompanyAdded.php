<?php

declare(strict_types=1);

namespace App\Company\Event;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Entity\Company\UserId;

/**
 * Доменное событие: контрагент добавлен в систему.
 *
 * Имя отражает бизнес-факт (добавление контрагента),
 * а не техническую CRUD-операцию.
 *
 * Содержит $userId, чтобы подписчики (например, нотификации,
 * аудит, синхронизация) знали, кто стал владельцем компании.
 */
final class CompanyAdded
{
    public readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly Id     $id,
        public readonly Name   $name,
        public readonly Inn    $inn,
        public readonly UserId $userId,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
