<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20201025131352 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ingr_bl DROP FOREIGN KEY FK_2FB736F3CD2087A9');
        $this->addSql('ALTER TABLE recept_bl_ordered DROP FOREIGN KEY FK_AA8159DBCD2087A9');
        $this->addSql('ALTER TABLE dagen_recepten DROP FOREIGN KEY FK_EA2DBC7CB904618A');
        $this->addSql('ALTER TABLE ingr_bl DROP FOREIGN KEY FK_2FB736F3BF75BFC5');
        $this->addSql('DROP TABLE boodschappenlijst');
        $this->addSql('DROP TABLE dag');
        $this->addSql('DROP TABLE dagen_recepten');
        $this->addSql('DROP TABLE ingr_bl');
        $this->addSql('DROP TABLE recept_bl_ordered');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE boodschappenlijst (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6DCF400AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dag (id INT AUTO_INCREMENT NOT NULL, menu_id INT DEFAULT NULL, INDEX IDX_1FAF14F3CCD7E912 (menu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dagen_recepten (dag_id INT NOT NULL, recept_id INT NOT NULL, INDEX IDX_EA2DBC7CB904618A (dag_id), INDEX IDX_EA2DBC7CC6BF5295 (recept_id), PRIMARY KEY(dag_id, recept_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingr_bl (id INT AUTO_INCREMENT NOT NULL, afd_id INT DEFAULT NULL, ro_id INT DEFAULT NULL, ingr_id INT DEFAULT NULL, bl_id INT DEFAULT NULL, servings INT NOT NULL, ingr_ingr LONGTEXT NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_2FB736F3CD2087A9 (bl_id), INDEX IDX_2FB736F3C298670A (ingr_id), INDEX IDX_2FB736F3495C83C7 (afd_id), INDEX IDX_2FB736F3BF75BFC5 (ro_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recept_bl_ordered (id INT AUTO_INCREMENT NOT NULL, recept_id INT DEFAULT NULL, bl_id INT DEFAULT NULL, positie INT NOT NULL, servings INT NOT NULL, datum DATE DEFAULT NULL, INDEX IDX_AA8159DBCD2087A9 (bl_id), INDEX IDX_AA8159DBC6BF5295 (recept_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE boodschappenlijst ADD CONSTRAINT FK_6DCF400AA76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id)');
        $this->addSql('ALTER TABLE dag ADD CONSTRAINT FK_1FAF14F3CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE dagen_recepten ADD CONSTRAINT FK_EA2DBC7CB904618A FOREIGN KEY (dag_id) REFERENCES dag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dagen_recepten ADD CONSTRAINT FK_EA2DBC7CC6BF5295 FOREIGN KEY (recept_id) REFERENCES recept (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingr_bl ADD CONSTRAINT FK_2FB736F3495C83C7 FOREIGN KEY (afd_id) REFERENCES afdeling (id)');
        $this->addSql('ALTER TABLE ingr_bl ADD CONSTRAINT FK_2FB736F3BF75BFC5 FOREIGN KEY (ro_id) REFERENCES recept_bl_ordered (id)');
        $this->addSql('ALTER TABLE ingr_bl ADD CONSTRAINT FK_2FB736F3C298670A FOREIGN KEY (ingr_id) REFERENCES ingredient (id)');
        $this->addSql('ALTER TABLE ingr_bl ADD CONSTRAINT FK_2FB736F3CD2087A9 FOREIGN KEY (bl_id) REFERENCES boodschappenlijst (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recept_bl_ordered ADD CONSTRAINT FK_AA8159DBC6BF5295 FOREIGN KEY (recept_id) REFERENCES recept (id)');
        $this->addSql('ALTER TABLE recept_bl_ordered ADD CONSTRAINT FK_AA8159DBCD2087A9 FOREIGN KEY (bl_id) REFERENCES boodschappenlijst (id) ON DELETE SET NULL');
    }
}
