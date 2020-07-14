<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200630134102 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yield_type (id INT AUTO_INCREMENT NOT NULL, unit_singular VARCHAR(255) NOT NULL, unit_plural VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recept ADD yieldtype_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recept ADD CONSTRAINT FK_B92268A168B7959F FOREIGN KEY (yieldtype_id) REFERENCES yield_type (id)');
        $this->addSql('CREATE INDEX IDX_B92268A168B7959F ON recept (yieldtype_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE recept DROP FOREIGN KEY FK_B92268A168B7959F');
        $this->addSql('DROP TABLE yield_type');
        $this->addSql('DROP INDEX IDX_B92268A168B7959F ON recept');
        $this->addSql('ALTER TABLE recept DROP yieldtype_id');
    }
}
