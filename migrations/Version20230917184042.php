<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230917184042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horoscope_text DROP FOREIGN KEY FK_2967C5C850C84A');
        $this->addSql('CREATE TABLE horoscope_text_published (id INT AUTO_INCREMENT NOT NULL, horoscope_text_id INT NOT NULL, astrological_sign_id INT NOT NULL, publish_date DATETIME NOT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_3C931CB59CA6D511 (horoscope_text_id), INDEX IDX_3C931CB51AE6728F (astrological_sign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE horoscope_text_published ADD CONSTRAINT FK_3C931CB59CA6D511 FOREIGN KEY (horoscope_text_id) REFERENCES horoscope_text (id)');
        $this->addSql('ALTER TABLE horoscope_text_published ADD CONSTRAINT FK_3C931CB51AE6728F FOREIGN KEY (astrological_sign_id) REFERENCES astrological_sign (id)');
        $this->addSql('DROP TABLE publish_date');
        $this->addSql('DROP INDEX IDX_2967C5C850C84A ON horoscope_text');
        $this->addSql('ALTER TABLE horoscope_text DROP published_on_date_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE publish_date (id INT AUTO_INCREMENT NOT NULL, astrological_sign_id INT NOT NULL, date DATETIME NOT NULL, note VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_78B553BA1AE6728F (astrological_sign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE publish_date ADD CONSTRAINT FK_78B553BA1AE6728F FOREIGN KEY (astrological_sign_id) REFERENCES astrological_sign (id)');
        $this->addSql('DROP TABLE horoscope_text_published');
        $this->addSql('ALTER TABLE horoscope_text ADD published_on_date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE horoscope_text ADD CONSTRAINT FK_2967C5C850C84A FOREIGN KEY (published_on_date_id) REFERENCES publish_date (id)');
        $this->addSql('CREATE INDEX IDX_2967C5C850C84A ON horoscope_text (published_on_date_id)');
    }
}
