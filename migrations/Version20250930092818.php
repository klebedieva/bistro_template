<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930092818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if (!$schema->hasTable('allergen')) {
            if ($isPostgres) {
                $this->addSql('CREATE TABLE allergen (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
                $this->addSql('CREATE UNIQUE INDEX UNIQ_25BF08CE77153098 ON allergen (code)');
            } else {
                $this->addSql('CREATE TABLE allergen (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_25BF08CE77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            }
        }
        if (!$schema->hasTable('menu_item_allergen')) {
            if ($isPostgres) {
                $this->addSql('CREATE TABLE menu_item_allergen (menu_item_id INT NOT NULL, allergen_id INT NOT NULL, PRIMARY KEY(menu_item_id, allergen_id))');
                $this->addSql('CREATE INDEX IDX_EF7195939AB44FE0 ON menu_item_allergen (menu_item_id)');
                $this->addSql('CREATE INDEX IDX_EF7195936E775A4A ON menu_item_allergen (allergen_id)');
            } else {
                $this->addSql('CREATE TABLE menu_item_allergen (menu_item_id INT NOT NULL, allergen_id INT NOT NULL, INDEX IDX_EF7195939AB44FE0 (menu_item_id), INDEX IDX_EF7195936E775A4A (allergen_id), PRIMARY KEY(menu_item_id, allergen_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            }
            $this->addSql('ALTER TABLE menu_item_allergen ADD CONSTRAINT FK_EF7195939AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_allergen ADD CONSTRAINT FK_EF7195936E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE menu_item_allergen DROP CONSTRAINT FK_EF7195939AB44FE0');
            $this->addSql('ALTER TABLE menu_item_allergen DROP CONSTRAINT FK_EF7195936E775A4A');
        } else {
            $this->addSql('ALTER TABLE menu_item_allergen DROP FOREIGN KEY FK_EF7195939AB44FE0');
            $this->addSql('ALTER TABLE menu_item_allergen DROP FOREIGN KEY FK_EF7195936E775A4A');
        }
        $this->addSql('DROP TABLE allergen');
        $this->addSql('DROP TABLE menu_item_allergen');
    }
}
