<?php

declare(strict_types=1);

namespace App\Data\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703054617 extends AbstractMigration
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
        $this->addSql('DROP INDEX idx_subscriptions_company_id');
        $this->addSql('DROP INDEX idx_subscriptions_company_status');
        $this->addSql('ALTER TABLE subscriptions RENAME COLUMN company_id TO user_id');
        $this->addSql('ALTER TABLE training_records ALTER id TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER worker_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER result TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER protocol_number TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER registry_number TYPE VARCHAR');
        $this->addSql('ALTER TABLE training_records ALTER program TYPE VARCHAR');
        $this->addSql('ALTER TABLE workers ALTER id TYPE VARCHAR');
        $this->addSql('ALTER TABLE workers ALTER company_id TYPE VARCHAR');
        $this->addSql('ALTER TABLE workers ALTER profession TYPE VARCHAR');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE companies ALTER user_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE companies ALTER user_id SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE subscriptions RENAME COLUMN user_id TO company_id');
        $this->addSql('CREATE INDEX idx_subscriptions_company_id ON subscriptions (company_id)');
        $this->addSql('CREATE INDEX idx_subscriptions_company_status ON subscriptions (company_id, status)');
        $this->addSql('ALTER TABLE training_records ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE training_records ALTER worker_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE training_records ALTER program TYPE INT');
        $this->addSql('ALTER TABLE training_records ALTER result TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE training_records ALTER protocol_number TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE training_records ALTER registry_number TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE workers ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE workers ALTER company_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE workers ALTER profession TYPE VARCHAR(200)');
    }
}
