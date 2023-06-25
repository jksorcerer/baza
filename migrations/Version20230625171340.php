<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625171340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_4C62E638F675F31B ON contact (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638F675F31B');
        $this->addSql('DROP INDEX IDX_4C62E638F675F31B ON contact');
        $this->addSql('ALTER TABLE contact DROP author_id');
    }
}
