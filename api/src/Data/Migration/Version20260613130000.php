<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Создание таблицы workers.
 */
final class Version20260613130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create workers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE workers (
                id          VARCHAR(36)  NOT NULL,
                company_id  VARCHAR(36)  NOT NULL,
                full_name   JSON         NOT NULL,
                profession  VARCHAR(200) NOT NULL,
                snils_info  JSON         NOT NULL,
                PRIMARY KEY (id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE workers');
    }
}
