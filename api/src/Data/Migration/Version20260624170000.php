<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Миграция: Добавляет поле status вместо is_archived в таблицу companies.
 */
final class Version20260624170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert is_archived boolean column to status enum string column in companies table.';
    }

    public function up(Schema $schema): void
    {
        // 1. Добавляем колонку status
        $this->addSql("ALTER TABLE companies ADD COLUMN status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE'");

        // 2. Мигрируем данные
        $this->addSql("UPDATE companies SET status = 'ARCHIVED' WHERE is_archived = TRUE");

        // 3. Удаляем старую колонку is_archived
        $this->addSql("ALTER TABLE companies DROP COLUMN is_archived");
    }

    public function down(Schema $schema): void
    {
        // 1. Восстанавливаем колонку is_archived
        $this->addSql("ALTER TABLE companies ADD COLUMN is_archived BOOLEAN NOT NULL DEFAULT FALSE");

        // 2. Мигрируем данные назад
        $this->addSql("UPDATE companies SET is_archived = TRUE WHERE status = 'ARCHIVED'");

        // 3. Удаляем колонку status
        $this->addSql("ALTER TABLE companies DROP COLUMN status");
    }
}
