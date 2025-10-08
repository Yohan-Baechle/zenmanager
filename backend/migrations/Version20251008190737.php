<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008190737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clock ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE clock ADD CONSTRAINT FK_BE7BBE927E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BE7BBE927E3C61F9 ON clock (owner_id)');
        $this->addSql('ALTER TABLE team ALTER description TYPE TEXT');
        $this->addSql('ALTER TABLE team ALTER manager_id DROP NOT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F783E3463 FOREIGN KEY (manager_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C4E0A61F783E3463 ON team (manager_id)');
        $this->addSql('ALTER TABLE "user" ADD team_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD8AE ON "user" (team_id)');
        $this->addSql('ALTER TABLE working_time ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE working_time ADD CONSTRAINT FK_31EE2ABF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_31EE2ABF7E3C61F9 ON working_time (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649296CD8AE');
        $this->addSql('DROP INDEX IDX_8D93D649296CD8AE');
        $this->addSql('ALTER TABLE "user" DROP team_id');
        $this->addSql('ALTER TABLE team DROP CONSTRAINT FK_C4E0A61F783E3463');
        $this->addSql('DROP INDEX IDX_C4E0A61F783E3463');
        $this->addSql('ALTER TABLE team ALTER manager_id SET NOT NULL');
        $this->addSql('ALTER TABLE team ALTER description TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE clock DROP CONSTRAINT FK_BE7BBE927E3C61F9');
        $this->addSql('DROP INDEX IDX_BE7BBE927E3C61F9');
        $this->addSql('ALTER TABLE clock DROP owner_id');
        $this->addSql('ALTER TABLE working_time DROP CONSTRAINT FK_31EE2ABF7E3C61F9');
        $this->addSql('DROP INDEX IDX_31EE2ABF7E3C61F9');
        $this->addSql('ALTER TABLE working_time DROP owner_id');
    }
}
