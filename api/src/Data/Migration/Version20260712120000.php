<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260712120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename subscription plan premium to extended';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE subscriptions SET plan = 'extended' WHERE plan = 'premium'");
        $this->addSql("UPDATE subscription_payments SET plan = 'extended' WHERE plan = 'premium'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE subscriptions SET plan = 'premium' WHERE plan = 'extended'");
        $this->addSql("UPDATE subscription_payments SET plan = 'premium' WHERE plan = 'extended'");
    }
}
