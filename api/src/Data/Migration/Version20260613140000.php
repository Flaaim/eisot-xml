<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Создание таблицы training_records.
 */
final class Version20260613140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create training_records table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE training_records (
                id              VARCHAR(36)  NOT NULL,
                worker_id       VARCHAR(36)  NOT NULL,
                program         VARCHAR(500) NOT NULL,
                result          VARCHAR(50)  NOT NULL,
                date            TIMESTAMP    NOT NULL,
                protocol_number VARCHAR(100) NOT NULL,
                registry_number VARCHAR(100) DEFAULT NULL,
                PRIMARY KEY (id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE training_records');
    }
}
