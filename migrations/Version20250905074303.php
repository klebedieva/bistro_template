<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905074303 extends AbstractMigration
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
            $this->addSql('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50), ALTER COLUMN role SET NOT NULL');
        } else {
            $this->addSql('ALTER TABLE users CHANGE role role VARCHAR(50) NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255), ALTER COLUMN role SET NOT NULL');
        } else {
            $this->addSql('ALTER TABLE users CHANGE role role VARCHAR(255) NOT NULL');
        }
    }
}
