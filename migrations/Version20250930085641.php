<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930085641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('tables')) {
            $platform = $this->connection->getDatabasePlatform();
            $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

            if ($isPostgres) {
                $this->addSql('CREATE TABLE tables (id SERIAL NOT NULL, name VARCHAR(100) NOT NULL, capacity INT NOT NULL, zone VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
            } else {
                $this->addSql('CREATE TABLE tables (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, capacity INT NOT NULL, zone VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tables');
    }
}
