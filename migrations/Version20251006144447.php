<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006144447 extends AbstractMigration
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
            $this->addSql('ALTER TABLE "order" ALTER COLUMN status TYPE VARCHAR(255), ALTER COLUMN status SET DEFAULT \'pending\', ALTER COLUMN status SET NOT NULL');
            $this->addSql('ALTER TABLE "order" ALTER COLUMN delivery_mode TYPE VARCHAR(255), ALTER COLUMN delivery_mode SET DEFAULT \'delivery\', ALTER COLUMN delivery_mode SET NOT NULL');
            $this->addSql('ALTER TABLE "order" ALTER COLUMN payment_mode TYPE VARCHAR(255), ALTER COLUMN payment_mode SET DEFAULT \'card\', ALTER COLUMN payment_mode SET NOT NULL');
        } else {
            $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(255) DEFAULT \'pending\' NOT NULL, CHANGE delivery_mode delivery_mode VARCHAR(255) DEFAULT \'delivery\' NOT NULL, CHANGE payment_mode payment_mode VARCHAR(255) DEFAULT \'card\' NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE "order" ALTER COLUMN status TYPE VARCHAR(20), ALTER COLUMN status DROP DEFAULT, ALTER COLUMN status SET NOT NULL');
            $this->addSql('ALTER TABLE "order" ALTER COLUMN delivery_mode TYPE VARCHAR(20), ALTER COLUMN delivery_mode DROP DEFAULT, ALTER COLUMN delivery_mode SET NOT NULL');
            $this->addSql('ALTER TABLE "order" ALTER COLUMN payment_mode TYPE VARCHAR(20), ALTER COLUMN payment_mode DROP DEFAULT, ALTER COLUMN payment_mode SET NOT NULL');
        } else {
            $this->addSql('ALTER TABLE `order` CHANGE status status VARCHAR(20) NOT NULL, CHANGE delivery_mode delivery_mode VARCHAR(20) NOT NULL, CHANGE payment_mode payment_mode VARCHAR(20) NOT NULL');
        }
    }
}
