<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923095704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('CREATE TABLE badge (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
            $this->addSql('CREATE TABLE menu_item_badge (menu_item_id INT NOT NULL, badge_id INT NOT NULL, PRIMARY KEY(menu_item_id, badge_id))');
            $this->addSql('CREATE INDEX IDX_F5FAE71F9AB44FE0 ON menu_item_badge (menu_item_id)');
            $this->addSql('CREATE INDEX IDX_F5FAE71FF7A2C2FC ON menu_item_badge (badge_id)');
            $this->addSql('CREATE TABLE menu_item_tag (menu_item_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(menu_item_id, tag_id))');
            $this->addSql('CREATE INDEX IDX_C8CD89279AB44FE0 ON menu_item_tag (menu_item_id)');
            $this->addSql('CREATE INDEX IDX_C8CD8927BAD26311 ON menu_item_tag (tag_id)');
            $this->addSql('CREATE TABLE tag (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
            $this->addSql('ALTER TABLE menu_item_badge ADD CONSTRAINT FK_F5FAE71F9AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_badge ADD CONSTRAINT FK_F5FAE71FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_tag ADD CONSTRAINT FK_C8CD89279AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_tag ADD CONSTRAINT FK_C8CD8927BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        } else {
            $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE TABLE menu_item_badge (menu_item_id INT NOT NULL, badge_id INT NOT NULL, INDEX IDX_F5FAE71F9AB44FE0 (menu_item_id), INDEX IDX_F5FAE71FF7A2C2FC (badge_id), PRIMARY KEY(menu_item_id, badge_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE TABLE menu_item_tag (menu_item_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C8CD89279AB44FE0 (menu_item_id), INDEX IDX_C8CD8927BAD26311 (tag_id), PRIMARY KEY(menu_item_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE menu_item_badge ADD CONSTRAINT FK_F5FAE71F9AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_badge ADD CONSTRAINT FK_F5FAE71FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_tag ADD CONSTRAINT FK_C8CD89279AB44FE0 FOREIGN KEY (menu_item_id) REFERENCES menu_item (id) ON DELETE CASCADE');
            $this->addSql('ALTER TABLE menu_item_tag ADD CONSTRAINT FK_C8CD8927BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE menu_item_badge DROP CONSTRAINT FK_F5FAE71F9AB44FE0');
            $this->addSql('ALTER TABLE menu_item_badge DROP CONSTRAINT FK_F5FAE71FF7A2C2FC');
            $this->addSql('ALTER TABLE menu_item_tag DROP CONSTRAINT FK_C8CD89279AB44FE0');
            $this->addSql('ALTER TABLE menu_item_tag DROP CONSTRAINT FK_C8CD8927BAD26311');
        } else {
            $this->addSql('ALTER TABLE menu_item_badge DROP FOREIGN KEY FK_F5FAE71F9AB44FE0');
            $this->addSql('ALTER TABLE menu_item_badge DROP FOREIGN KEY FK_F5FAE71FF7A2C2FC');
            $this->addSql('ALTER TABLE menu_item_tag DROP FOREIGN KEY FK_C8CD89279AB44FE0');
            $this->addSql('ALTER TABLE menu_item_tag DROP FOREIGN KEY FK_C8CD8927BAD26311');
        }
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE menu_item_badge');
        $this->addSql('DROP TABLE menu_item_tag');
        $this->addSql('DROP TABLE tag');
    }
}
