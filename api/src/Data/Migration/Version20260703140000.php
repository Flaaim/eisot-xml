<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subscriptions table for Billing/Subscription subdomain';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE subscriptions (
                    id VARCHAR NOT NULL,
                    user_id VARCHAR NOT NULL,
                    plan VARCHAR(16) NOT NULL,
                    status VARCHAR(16) NOT NULL DEFAULT 'active',
                    period_start DATE NOT NULL,
                    period_end DATE NOT NULL,
                    PRIMARY KEY (id)
                )
            SQL);
        $this->addSql('CREATE INDEX idx_subscriptions_user_id ON subscriptions (user_id)');
        $this->addSql('CREATE INDEX idx_subscriptions_user_status ON subscriptions (user_id, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscriptions');
    }
}
