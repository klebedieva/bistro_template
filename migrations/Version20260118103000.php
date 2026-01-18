<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260118103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prefix gallery image paths with gallery/ subfolder';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql("UPDATE gallery_images SET image_path = 'gallery/' || image_path WHERE image_path IS NOT NULL AND image_path <> '' AND image_path NOT LIKE 'gallery/%' AND image_path NOT LIKE 'uploads/%' AND image_path NOT LIKE '/%';");
        } else {
            $this->addSql("UPDATE gallery_images SET image_path = CONCAT('gallery/', image_path) WHERE image_path IS NOT NULL AND image_path <> '' AND image_path NOT LIKE 'gallery/%' AND image_path NOT LIKE 'uploads/%' AND image_path NOT LIKE '/%';");
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql("UPDATE gallery_images SET image_path = SUBSTRING(image_path FROM 9) WHERE image_path LIKE 'gallery/%';");
        } else {
            $this->addSql("UPDATE gallery_images SET image_path = SUBSTRING(image_path, 9) WHERE image_path LIKE 'gallery/%';");
        }
    }
}
