<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230916101548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publish_date ADD astrological_sign_id INT NOT NULL');
        $this->addSql('ALTER TABLE publish_date ADD CONSTRAINT FK_78B553BA1AE6728F FOREIGN KEY (astrological_sign_id) REFERENCES astrological_sign (id)');
        $this->addSql('CREATE INDEX IDX_78B553BA1AE6728F ON publish_date (astrological_sign_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publish_date DROP FOREIGN KEY FK_78B553BA1AE6728F');
        $this->addSql('DROP INDEX IDX_78B553BA1AE6728F ON publish_date');
        $this->addSql('ALTER TABLE publish_date DROP astrological_sign_id');
    }
}
