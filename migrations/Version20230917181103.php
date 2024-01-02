<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230917181103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE horoscope_text_publish_date');
        $this->addSql('ALTER TABLE horoscope_text ADD published_on_date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE horoscope_text ADD CONSTRAINT FK_2967C5C850C84A FOREIGN KEY (published_on_date_id) REFERENCES publish_date (id)');
        $this->addSql('CREATE INDEX IDX_2967C5C850C84A ON horoscope_text (published_on_date_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE horoscope_text_publish_date (horoscope_text_id INT NOT NULL, publish_date_id INT NOT NULL, INDEX IDX_67AA52639CA6D511 (horoscope_text_id), INDEX IDX_67AA52638415F71A (publish_date_id), PRIMARY KEY(horoscope_text_id, publish_date_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE horoscope_text_publish_date ADD CONSTRAINT FK_67AA52638415F71A FOREIGN KEY (publish_date_id) REFERENCES publish_date (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horoscope_text_publish_date ADD CONSTRAINT FK_67AA52639CA6D511 FOREIGN KEY (horoscope_text_id) REFERENCES horoscope_text (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horoscope_text DROP FOREIGN KEY FK_2967C5C850C84A');
        $this->addSql('DROP INDEX IDX_2967C5C850C84A ON horoscope_text');
        $this->addSql('ALTER TABLE horoscope_text DROP published_on_date_id');
    }
}
