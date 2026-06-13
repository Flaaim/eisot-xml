<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет колонку is_archived в таблицу companies для поддержки мягкого удаления.
 */
final class Version20260613000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_archived column to companies table for soft delete support.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE companies ADD COLUMN is_archived BOOLEAN NOT NULL DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE companies DROP COLUMN is_archived');
    }
}
