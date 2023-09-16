<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230421124652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE daily_content DROP COLUMN horoscope');
//        $this->addSql('ALTER TABLE daily_content DROP COLUMN horoscope_second');
        $this->addSql('ALTER TABLE daily_content ADD horoscope_id INT DEFAULT NULL, ADD horoscope_addendum_id INT DEFAULT NULL, DROP horoscope, DROP horoscope_second');
        $this->addSql('ALTER TABLE daily_content ADD CONSTRAINT FK_2124E15DEAC823EF FOREIGN KEY (horoscope_id) REFERENCES horoscope_final (id)');
        $this->addSql('ALTER TABLE daily_content ADD CONSTRAINT FK_2124E15D36876FAC FOREIGN KEY (horoscope_addendum_id) REFERENCES horoscope_final (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2124E15DEAC823EF ON daily_content (horoscope_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2124E15D36876FAC ON daily_content (horoscope_addendum_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_content DROP FOREIGN KEY FK_2124E15DEAC823EF');
        $this->addSql('ALTER TABLE daily_content DROP FOREIGN KEY FK_2124E15D36876FAC');
        $this->addSql('DROP INDEX UNIQ_2124E15DEAC823EF ON daily_content');
        $this->addSql('DROP INDEX UNIQ_2124E15D36876FAC ON daily_content');
        $this->addSql('ALTER TABLE daily_content ADD horoscope LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD horoscope_second LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP horoscope_id, DROP horoscope_addendum_id');
    }
}
