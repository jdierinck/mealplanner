<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200629134838 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE menu_recepten');
        $this->addSql('DROP TABLE recept_ordered');
        $this->addSql('ALTER TABLE recept CHANGE personen yield INT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE menu_recepten (menu_id INT NOT NULL, recept_id INT NOT NULL, INDEX IDX_2B7D5934CCD7E912 (menu_id), INDEX IDX_2B7D5934C6BF5295 (recept_id), PRIMARY KEY(menu_id, recept_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recept_ordered (id INT AUTO_INCREMENT NOT NULL, recept_id INT DEFAULT NULL, menu_id INT DEFAULT NULL, positie INT NOT NULL, INDEX IDX_3E57D52CCCD7E912 (menu_id), INDEX IDX_3E57D52CC6BF5295 (recept_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_recepten ADD CONSTRAINT FK_2B7D5934C6BF5295 FOREIGN KEY (recept_id) REFERENCES recept (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_recepten ADD CONSTRAINT FK_2B7D5934CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recept_ordered ADD CONSTRAINT FK_3E57D52CC6BF5295 FOREIGN KEY (recept_id) REFERENCES recept (id)');
        $this->addSql('ALTER TABLE recept_ordered ADD CONSTRAINT FK_3E57D52CCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recept CHANGE yield personen INT NOT NULL');
    }
}
