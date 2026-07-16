<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add trial_used flag to users for one-time Trial Subscription';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD trial_used BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP trial_used');
    }
}
