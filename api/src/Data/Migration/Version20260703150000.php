<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move User Subscription from company_id to user_id (upgrade path)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM information_schema.columns
                        WHERE table_name = 'subscriptions' AND column_name = 'company_id'
                    ) THEN
                        ALTER TABLE subscriptions RENAME COLUMN company_id TO user_id;
                    END IF;
                END $$;
            SQL);
        $this->addSql('DROP INDEX IF EXISTS idx_subscriptions_company_id');
        $this->addSql('DROP INDEX IF EXISTS idx_subscriptions_company_status');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_subscriptions_user_id ON subscriptions (user_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status ON subscriptions (user_id, status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_subscriptions_user_id');
        $this->addSql('DROP INDEX IF EXISTS idx_subscriptions_user_status');
        $this->addSql(<<<'SQL'
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM information_schema.columns
                        WHERE table_name = 'subscriptions' AND column_name = 'user_id'
                    ) THEN
                        ALTER TABLE subscriptions RENAME COLUMN user_id TO company_id;
                    END IF;
                END $$;
            SQL);
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_subscriptions_company_id ON subscriptions (company_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_subscriptions_company_status ON subscriptions (company_id, status)');
    }
}
