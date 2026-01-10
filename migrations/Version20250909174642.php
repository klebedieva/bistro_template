<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909174642 extends AbstractMigration
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
            $this->addSql('ALTER TABLE reviews ADD consent BOOLEAN NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN name TYPE VARCHAR(80), ALTER COLUMN name SET NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN email TYPE VARCHAR(180), ALTER COLUMN email DROP NOT NULL, ALTER COLUMN email SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE reviews ADD consent TINYINT(1) NOT NULL, CHANGE name name VARCHAR(80) NOT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE reviews DROP consent');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN name TYPE VARCHAR(255), ALTER COLUMN name SET NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN email TYPE VARCHAR(255), ALTER COLUMN email DROP NOT NULL, ALTER COLUMN email SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE reviews DROP consent, CHANGE name name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        }
    }
}
