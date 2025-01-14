<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202091009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__coaster AS SELECT id, name, max_speed, length, max_height, operating FROM coaster');
        $this->addSql('DROP TABLE coaster');
        $this->addSql('CREATE TABLE coaster (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, park_id INTEGER DEFAULT NULL, name VARCHAR(80) NOT NULL, max_speed INTEGER DEFAULT NULL, length INTEGER DEFAULT NULL, max_height INTEGER DEFAULT NULL, operating BOOLEAN NOT NULL, CONSTRAINT FK_F6312A7844990C25 FOREIGN KEY (park_id) REFERENCES park (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO coaster (id, name, max_speed, length, max_height, operating) SELECT id, name, max_speed, length, max_height, operating FROM __temp__coaster');
        $this->addSql('DROP TABLE __temp__coaster');
        $this->addSql('CREATE INDEX IDX_F6312A7844990C25 ON coaster (park_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__coaster AS SELECT id, name, max_speed, length, max_height, operating FROM coaster');
        $this->addSql('DROP TABLE coaster');
        $this->addSql('CREATE TABLE coaster (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(80) NOT NULL, max_speed INTEGER DEFAULT NULL, length INTEGER DEFAULT NULL, max_height INTEGER DEFAULT NULL, operating BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO coaster (id, name, max_speed, length, max_height, operating) SELECT id, name, max_speed, length, max_height, operating FROM __temp__coaster');
        $this->addSql('DROP TABLE __temp__coaster');
    }
}
