<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251105120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add non-destructive performance indexes for reviews, orders and gallery images tables';
    }

    public function up(Schema $schema): void
    {
        // reviews
        if ($schema->hasTable('reviews')) {
            $table = $schema->getTable('reviews');
            if (!$table->hasIndex('idx_reviews_menu_item')) {
                $table->addIndex(['menu_item_id'], 'idx_reviews_menu_item');
            }
            if (!$table->hasIndex('idx_reviews_is_approved')) {
                $table->addIndex(['is_approved'], 'idx_reviews_is_approved');
            }
            if (!$table->hasIndex('idx_reviews_created_at')) {
                $table->addIndex(['created_at'], 'idx_reviews_created_at');
            }
        }

        // order (reserved keyword; actual table name is `order`)
        if ($schema->hasTable('order')) {
            $table = $schema->getTable('order');
            if (!$table->hasIndex('idx_order_status')) {
                $table->addIndex(['status'], 'idx_order_status');
            }
            if (!$table->hasIndex('idx_order_created_at')) {
                $table->addIndex(['created_at'], 'idx_order_created_at');
            }
        }

        // gallery_images
        if ($schema->hasTable('gallery_images')) {
            $table = $schema->getTable('gallery_images');
            if (!$table->hasIndex('idx_gallery_images_is_active')) {
                $table->addIndex(['is_active'], 'idx_gallery_images_is_active');
            }
            if (!$table->hasIndex('idx_gallery_images_category')) {
                $table->addIndex(['category'], 'idx_gallery_images_category');
            }
            if (!$table->hasIndex('idx_gallery_images_created_at')) {
                $table->addIndex(['created_at'], 'idx_gallery_images_created_at');
            }
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('reviews')) {
            $table = $schema->getTable('reviews');
            if ($table->hasIndex('idx_reviews_menu_item')) {
                $table->dropIndex('idx_reviews_menu_item');
            }
            if ($table->hasIndex('idx_reviews_is_approved')) {
                $table->dropIndex('idx_reviews_is_approved');
            }
            if ($table->hasIndex('idx_reviews_created_at')) {
                $table->dropIndex('idx_reviews_created_at');
            }
        }

        if ($schema->hasTable('order')) {
            $table = $schema->getTable('order');
            if ($table->hasIndex('idx_order_status')) {
                $table->dropIndex('idx_order_status');
            }
            if ($table->hasIndex('idx_order_created_at')) {
                $table->dropIndex('idx_order_created_at');
            }
        }

        if ($schema->hasTable('gallery_images')) {
            $table = $schema->getTable('gallery_images');
            if ($table->hasIndex('idx_gallery_images_is_active')) {
                $table->dropIndex('idx_gallery_images_is_active');
            }
            if ($table->hasIndex('idx_gallery_images_category')) {
                $table->dropIndex('idx_gallery_images_category');
            }
            if ($table->hasIndex('idx_gallery_images_created_at')) {
                $table->dropIndex('idx_gallery_images_created_at');
            }
        }
    }
}


