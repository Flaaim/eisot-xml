<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет колонку user_id в таблицу companies.
 *
 * Связь между Bounded Contexts Company и Auth осуществляется
 * исключительно по UUID-идентификатору пользователя.
 * Внешний ключ намеренно НЕ создаётся: контексты независимы.
 */
final class Version20260613120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id column to companies table for owner binding.';
    }

    public function up(Schema $schema): void
    {
        // Сначала добавляем колонку как nullable для обратной совместимости с существующими записями
        $this->addSql("ALTER TABLE companies ADD COLUMN user_id VARCHAR(36) NOT NULL DEFAULT ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE companies DROP COLUMN user_id');
    }
}
