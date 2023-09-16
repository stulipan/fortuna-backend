<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420212031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE daily_content (id INT AUTO_INCREMENT NOT NULL, quote_id INT DEFAULT NULL, horoscope LONGTEXT DEFAULT NULL, horoscope_second LONGTEXT DEFAULT NULL, INDEX IDX_2124E15DDB805178 (quote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE horoscope_raw (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, aries LONGTEXT DEFAULT NULL, taurus LONGTEXT DEFAULT NULL, gemini LONGTEXT DEFAULT NULL, cancer LONGTEXT DEFAULT NULL, leo LONGTEXT DEFAULT NULL, virgo LONGTEXT DEFAULT NULL, libra LONGTEXT DEFAULT NULL, scorpio LONGTEXT DEFAULT NULL, sagittarius LONGTEXT DEFAULT NULL, capricorn LONGTEXT DEFAULT NULL, aquarius LONGTEXT DEFAULT NULL, pisces LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, quote LONGTEXT NOT NULL, author LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE daily_content ADD CONSTRAINT FK_2124E15DDB805178 FOREIGN KEY (quote_id) REFERENCES quote (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_content DROP FOREIGN KEY FK_2124E15DDB805178');
        $this->addSql('DROP TABLE daily_content');
        $this->addSql('DROP TABLE horoscope_raw');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
