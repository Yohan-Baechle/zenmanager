<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015100226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clock_request (id SERIAL NOT NULL, user_id INT NOT NULL, target_clock_id INT DEFAULT NULL, reviewed_by_id INT DEFAULT NULL, type VARCHAR(10) NOT NULL, requested_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, requested_status BOOLEAN DEFAULT NULL, status VARCHAR(20) NOT NULL, reason TEXT NOT NULL, reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C22A2E4A76ED395 ON clock_request (user_id)');
        $this->addSql('CREATE INDEX IDX_9C22A2E4FFEC52FB ON clock_request (target_clock_id)');
        $this->addSql('CREATE INDEX IDX_9C22A2E4FC6B21F1 ON clock_request (reviewed_by_id)');
        $this->addSql('COMMENT ON COLUMN clock_request.requested_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN clock_request.reviewed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN clock_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN clock_request.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE clock_request ADD CONSTRAINT FK_9C22A2E4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clock_request ADD CONSTRAINT FK_9C22A2E4FFEC52FB FOREIGN KEY (target_clock_id) REFERENCES clock (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clock_request ADD CONSTRAINT FK_9C22A2E4FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE clock_request DROP CONSTRAINT FK_9C22A2E4A76ED395');
        $this->addSql('ALTER TABLE clock_request DROP CONSTRAINT FK_9C22A2E4FFEC52FB');
        $this->addSql('ALTER TABLE clock_request DROP CONSTRAINT FK_9C22A2E4FC6B21F1');
        $this->addSql('DROP TABLE clock_request');
    }
}
