<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Преобразование столбца program из VARCHAR (строка с названием) в INTEGER (ID программы Минтруда).
 */
final class Version20260614112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert training_records.program from VARCHAR to INTEGER (Mintrud program ID)';
    }

    public function up(Schema $schema): void
    {
        // 1. Добавить временный столбец
        $this->addSql('ALTER TABLE training_records ADD COLUMN program_new INTEGER');

        // 2. Смаппить старые строки в числовые ID по префиксу «N. »
        $this->addSql("
            UPDATE training_records SET program_new = CASE
                WHEN program LIKE '1.%'  THEN 1
                WHEN program LIKE '2.%'  THEN 2
                WHEN program LIKE '3.%'  THEN 3
                WHEN program LIKE '4.%'  THEN 4
                WHEN program LIKE '6.%'  THEN 6
                WHEN program LIKE '7.%'  THEN 7
                WHEN program LIKE '8.%'  THEN 8
                WHEN program LIKE '9.%'  THEN 9
                WHEN program LIKE '10.%' THEN 10
                WHEN program LIKE '11.%' THEN 11
                WHEN program LIKE '12.%' THEN 12
                WHEN program LIKE '13.%' THEN 13
                WHEN program LIKE '14.%' THEN 14
                WHEN program LIKE '15.%' THEN 15
                WHEN program LIKE '16.%' THEN 16
                WHEN program LIKE '17.%' THEN 17
                WHEN program LIKE '18.%' THEN 18
                WHEN program LIKE '19.%' THEN 19
                WHEN program LIKE '20.%' THEN 20
                WHEN program LIKE '21.%' THEN 21
                WHEN program LIKE '22.%' THEN 22
                WHEN program LIKE '23.%' THEN 23
                WHEN program LIKE '24.%' THEN 24
                WHEN program LIKE '25.%' THEN 25
                WHEN program LIKE '26.%' THEN 26
                WHEN program LIKE '27.%' THEN 27
                WHEN program LIKE '28.%' THEN 28
                WHEN program LIKE '29.%' THEN 29
            END
        ");

        // 3. Заменить столбец
        $this->addSql('ALTER TABLE training_records DROP COLUMN program');
        $this->addSql('ALTER TABLE training_records RENAME COLUMN program_new TO program');
        $this->addSql('ALTER TABLE training_records ALTER COLUMN program SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Обратное преобразование не реализовано — данные о названии утеряны.
        // При необходимости можно восстановить из каталога Program::CATALOG.
        $this->addSql('ALTER TABLE training_records ALTER COLUMN program TYPE VARCHAR(500) USING program::VARCHAR(500)');
    }
}
