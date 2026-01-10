<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926095358 extends AbstractMigration
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
            $this->addSql('ALTER TABLE menu_item ALTER COLUMN ingredients TYPE TEXT, ALTER COLUMN ingredients DROP NOT NULL, ALTER COLUMN ingredients SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE menu_item CHANGE ingredients ingredients LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE menu_item ALTER COLUMN ingredients TYPE JSON, ALTER COLUMN ingredients DROP NOT NULL, ALTER COLUMN ingredients SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE menu_item CHANGE ingredients ingredients JSON DEFAULT NULL');
        }
    }
}
