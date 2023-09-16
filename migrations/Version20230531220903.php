<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230531220903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horoscope_final ADD locale VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE horoscope_raw ADD locale VARCHAR(2) NOT NULL');
        $this->addSql('UPDATE horoscope_raw SET locale = \'hu\'');
        $this->addSql('UPDATE horoscope_final SET locale = \'hu\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horoscope_final DROP locale');
        $this->addSql('ALTER TABLE horoscope_raw DROP locale');
    }
}
