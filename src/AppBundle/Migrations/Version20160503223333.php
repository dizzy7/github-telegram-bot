<?php

namespace AppBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160503223333 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE repository_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tags_subsription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, username VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE repository (id INT NOT NULL, username VARCHAR(255) NOT NULL, repository VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tags_subsription (id INT NOT NULL, user_id INT DEFAULT NULL, repository_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DDE81C6DA76ED395 ON tags_subsription (user_id)');
        $this->addSql('CREATE INDEX IDX_DDE81C6D50C9D4F7 ON tags_subsription (repository_id)');
        $this->addSql('ALTER TABLE tags_subsription ADD CONSTRAINT FK_DDE81C6DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tags_subsription ADD CONSTRAINT FK_DDE81C6D50C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE tags_subsription DROP CONSTRAINT FK_DDE81C6DA76ED395');
        $this->addSql('ALTER TABLE tags_subsription DROP CONSTRAINT FK_DDE81C6D50C9D4F7');
        $this->addSql('DROP SEQUENCE repository_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tags_subsription_id_seq CASCADE');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE repository');
        $this->addSql('DROP TABLE tags_subsription');
    }
}
