<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024161627 extends AbstractMigration
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
            $this->addSql('CREATE TABLE coupon (id SERIAL NOT NULL, code VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, discount_type VARCHAR(20) NOT NULL, discount_value NUMERIC(10, 2) NOT NULL, min_order_amount NUMERIC(10, 2) DEFAULT NULL, max_discount NUMERIC(10, 2) DEFAULT NULL, usage_limit INT DEFAULT NULL, usage_count INT DEFAULT 0 NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_64BF3F0277153098 ON coupon (code)');
            $this->addSql('ALTER TABLE "order" ADD coupon_id INT DEFAULT NULL, ADD discount_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
            $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F529939866C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE SET NULL');
            $this->addSql('CREATE INDEX IDX_F529939866C5951B ON "order" (coupon_id)');
        } else {
            $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, discount_type VARCHAR(20) NOT NULL, discount_value NUMERIC(10, 2) NOT NULL, min_order_amount NUMERIC(10, 2) DEFAULT NULL, max_discount NUMERIC(10, 2) DEFAULT NULL, usage_limit INT DEFAULT NULL, usage_count INT DEFAULT 0 NOT NULL, valid_from DATETIME DEFAULT NULL, valid_until DATETIME DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_64BF3F0277153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE `order` ADD coupon_id INT DEFAULT NULL, ADD discount_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
            $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939866C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE SET NULL');
            $this->addSql('CREATE INDEX IDX_F529939866C5951B ON `order` (coupon_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F529939866C5951B');
            $this->addSql('DROP INDEX IF EXISTS IDX_F529939866C5951B ON "order"');
        } else {
            $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939866C5951B');
            $this->addSql('DROP INDEX IDX_F529939866C5951B ON `order`');
        }
        $this->addSql('DROP TABLE coupon');
        if ($isPostgres) {
            $this->addSql('ALTER TABLE "order" DROP coupon_id, DROP discount_amount');
        } else {
            $this->addSql('ALTER TABLE `order` DROP coupon_id, DROP discount_amount');
        }
    }
}
