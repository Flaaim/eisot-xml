<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716054710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE companies ALTER user_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE companies ALTER user_id DROP DEFAULT');
        $this->addSql('DROP INDEX idx_subscription_payments_status');
        $this->addSql('DROP INDEX idx_subscription_payments_user_id');
        $this->addSql('ALTER TABLE subscription_payments ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE subscription_payments ALTER amount_currency DROP DEFAULT');
        $this->addSql('ALTER INDEX uniq_subscription_payments_external_id RENAME TO UNIQ_27CC41E9F75D7B0');
        $this->addSql('DROP INDEX idx_subscriptions_user_status');
        $this->addSql('DROP INDEX idx_subscriptions_user_id');
        $this->addSql('ALTER TABLE training_records ALTER id TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER worker_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER result TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER protocol_number TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER registry_number TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER program TYPE VARCHAR');
        $this->addSql('ALTER TABLE user_networks ALTER identity TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE workers ALTER id TYPE VARCHAR');
        $this->addSql('ALTER TABLE workers ALTER company_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE workers ALTER profession TYPE VARCHAR');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE companies ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE companies ALTER user_id SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE subscription_payments ALTER amount_currency SET DEFAULT \'RUB\'');
        $this->addSql('ALTER TABLE subscription_payments ALTER status SET DEFAULT \'pending\'');
        $this->addSql('CREATE INDEX idx_subscription_payments_status ON subscription_payments (status)');
        $this->addSql('CREATE INDEX idx_subscription_payments_user_id ON subscription_payments (user_id)');
        $this->addSql('ALTER INDEX uniq_27cc41e9f75d7b0 RENAME TO uniq_subscription_payments_external_id');
        $this->addSql('CREATE INDEX idx_subscriptions_user_status ON subscriptions (user_id, status)');
        $this->addSql('CREATE INDEX idx_subscriptions_user_id ON subscriptions (user_id)');
        $this->addSql('ALTER TABLE training_records ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE training_records ALTER worker_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE training_records ALTER program TYPE INT');
        $this->addSql('ALTER TABLE training_records ALTER result TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE training_records ALTER protocol_number TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE training_records ALTER registry_number TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE user_networks ALTER identity TYPE VARCHAR(16)');
        $this->addSql('ALTER TABLE workers ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE workers ALTER company_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE workers ALTER profession TYPE VARCHAR(200)');
    }
}
