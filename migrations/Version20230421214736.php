<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421214736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE astrological_sign (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE horoscope_final ADD astrological_sign_id INT NOT NULL, ADD content LONGTEXT DEFAULT NULL, DROP aries, DROP taurus, DROP gemini, DROP cancer, DROP leo, DROP virgo, DROP libra, DROP scorpio, DROP sagittarius, DROP capricorn, DROP aquarius, DROP pisces');
        $this->addSql('ALTER TABLE horoscope_final ADD CONSTRAINT FK_7C78AD351AE6728F FOREIGN KEY (astrological_sign_id) REFERENCES astrological_sign (id)');
        $this->addSql('CREATE INDEX IDX_7C78AD351AE6728F ON horoscope_final (astrological_sign_id)');
        $this->addSql('ALTER TABLE horoscope_raw ADD astrological_sign_id INT NOT NULL, ADD content LONGTEXT DEFAULT NULL, DROP aries, DROP taurus, DROP gemini, DROP cancer, DROP leo, DROP virgo, DROP libra, DROP scorpio, DROP sagittarius, DROP capricorn, DROP aquarius, DROP pisces');
        $this->addSql('ALTER TABLE horoscope_raw ADD CONSTRAINT FK_4138840C1AE6728F FOREIGN KEY (astrological_sign_id) REFERENCES astrological_sign (id)');
        $this->addSql('CREATE INDEX IDX_4138840C1AE6728F ON horoscope_raw (astrological_sign_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horoscope_final DROP FOREIGN KEY FK_7C78AD351AE6728F');
        $this->addSql('ALTER TABLE horoscope_raw DROP FOREIGN KEY FK_4138840C1AE6728F');
        $this->addSql('DROP TABLE astrological_sign');
        $this->addSql('DROP INDEX IDX_7C78AD351AE6728F ON horoscope_final');
        $this->addSql('ALTER TABLE horoscope_final ADD taurus LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD gemini LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD cancer LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD leo LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD virgo LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD libra LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD scorpio LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD sagittarius LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD capricorn LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD aquarius LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD pisces LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP astrological_sign_id, CHANGE content aries LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX IDX_4138840C1AE6728F ON horoscope_raw');
        $this->addSql('ALTER TABLE horoscope_raw ADD taurus LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD gemini LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD cancer LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD leo LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD virgo LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD libra LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD scorpio LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD sagittarius LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD capricorn LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD aquarius LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD pisces LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP astrological_sign_id, CHANGE content aries LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
