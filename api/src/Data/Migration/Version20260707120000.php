<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subscription_payments table for YooKassa billing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE subscription_payments (
                    id VARCHAR NOT NULL,
                    external_id VARCHAR(64) NOT NULL,
                    user_id VARCHAR NOT NULL,
                    plan VARCHAR(16) NOT NULL,
                    status VARCHAR(16) NOT NULL DEFAULT 'pending',
                    amount_value NUMERIC(10, 2) NOT NULL,
                    amount_currency VARCHAR(3) NOT NULL DEFAULT 'RUB',
                    duration_days INT NOT NULL,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                    confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY (id)
                )
            SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_subscription_payments_external_id ON subscription_payments (external_id)');
        $this->addSql('CREATE INDEX idx_subscription_payments_user_id ON subscription_payments (user_id)');
        $this->addSql('CREATE INDEX idx_subscription_payments_status ON subscription_payments (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscription_payments');
    }
}
