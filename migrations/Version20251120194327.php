<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Update menu_item.image to store only filename (extract from paths)
 * 
 * This migration normalizes image paths in menu_item table:
 * - Extracts filename from paths like /uploads/menu/filename.jpg → filename.jpg
 * - Extracts filename from paths like /static/img/menu/filename.jpg → filename.jpg
 * - Leaves simple filenames unchanged (already correct format)
 */
final class Version20251120194327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize menu_item.image to store only filename (extract from full paths)';
    }

    public function up(Schema $schema): void
    {
        // Extract filename from paths containing /uploads/menu/
        // Example: /uploads/menu/entree-1.png → entree-1.png
        $this->addSql("
            UPDATE menu_item 
            SET image = SUBSTRING_INDEX(image, '/', -1)
            WHERE image LIKE '%/uploads/menu/%' 
               OR image LIKE '%uploads/menu/%'
        ");

        // Extract filename from paths containing /static/img/menu/ or /static/img/{category}/
        // Example: /static/img/menu/plat-1.png → plat-1.png
        // Note: This also handles old paths with category folders (entrees, plats, desserts)
        $this->addSql("
            UPDATE menu_item 
            SET image = SUBSTRING_INDEX(image, '/', -1)
            WHERE image LIKE '%/static/img/%' 
               OR image LIKE '%static/img/%'
        ");

        // Extract filename from any path that contains a slash (likely a full path)
        // This catches any remaining full paths
        // Example: /var/www/.../public/uploads/menu/file.jpg → file.jpg
        $this->addSql("
            UPDATE menu_item 
            SET image = SUBSTRING_INDEX(image, '/', -1)
            WHERE image LIKE '%/%' 
              AND image NOT LIKE 'http%'
              AND image NOT LIKE 'https%'
        ");
    }

    public function down(Schema $schema): void
    {
        // Cannot reverse this migration - we don't know the original paths
        // This is a data normalization, not a schema change
        $this->addSql('-- Cannot reverse: original paths are unknown');
    }
}
