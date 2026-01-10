<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916084304 extends AbstractMigration
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
            $this->addSql('ALTER TABLE reservations ADD status VARCHAR(20) NOT NULL, ADD is_confirmed BOOLEAN NOT NULL, ADD confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ADD confirmation_message TEXT DEFAULT NULL');
            $this->addSql('COMMENT ON COLUMN reservations.confirmed_at IS \'(DC2Type:datetime_immutable)\'');
        } else {
            $this->addSql('ALTER TABLE reservations ADD status VARCHAR(20) NOT NULL, ADD is_confirmed TINYINT(1) NOT NULL, ADD confirmed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD confirmation_message LONGTEXT DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('COMMENT ON COLUMN reservations.confirmed_at IS NULL');
        }
        $this->addSql('ALTER TABLE reservations DROP status, DROP is_confirmed, DROP confirmed_at, DROP confirmation_message');
    }
}
