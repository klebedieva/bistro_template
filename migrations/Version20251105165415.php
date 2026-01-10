<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105165415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        // Drop indexes only if they exist
        if ($schema->hasTable('contact_message') && $schema->getTable('contact_message')->hasIndex('idx_contact_msg_replied_by')) {
            if ($isPostgres) {
                $this->addSql('DROP INDEX IF EXISTS idx_contact_msg_replied_by');
            } else {
                $this->addSql('DROP INDEX idx_contact_msg_replied_by ON contact_message');
            }
        }
        if ($schema->hasTable('gallery_images')) {
            $table = $schema->getTable('gallery_images');
            if ($table->hasIndex('idx_gallery_images_category')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_gallery_images_category');
                } else {
                    $this->addSql('DROP INDEX idx_gallery_images_category ON gallery_images');
                }
            }
            if ($table->hasIndex('idx_gallery_images_created_at')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_gallery_images_created_at');
                } else {
                    $this->addSql('DROP INDEX idx_gallery_images_created_at ON gallery_images');
                }
            }
            if ($table->hasIndex('idx_gallery_images_is_active')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_gallery_images_is_active');
                } else {
                    $this->addSql('DROP INDEX idx_gallery_images_is_active ON gallery_images');
                }
            }
        }
        if ($schema->hasTable('menu_item_allergen')) {
            $table = $schema->getTable('menu_item_allergen');
            if ($table->hasIndex('idx_menu_item_allergen_allergen_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_allergen_allergen_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_allergen_allergen_id ON menu_item_allergen');
                }
            }
            if ($table->hasIndex('idx_menu_item_allergen_menu_item_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_allergen_menu_item_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_allergen_menu_item_id ON menu_item_allergen');
                }
            }
            if ($table->hasIndex('idx_mi_allergen_pair')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_mi_allergen_pair');
                } else {
                    $this->addSql('DROP INDEX idx_mi_allergen_pair ON menu_item_allergen');
                }
            }
        }
        if ($schema->hasTable('menu_item_badge')) {
            $table = $schema->getTable('menu_item_badge');
            if ($table->hasIndex('idx_menu_item_badge_badge_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_badge_badge_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_badge_badge_id ON menu_item_badge');
                }
            }
            if ($table->hasIndex('idx_menu_item_badge_menu_item_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_badge_menu_item_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_badge_menu_item_id ON menu_item_badge');
                }
            }
            if ($table->hasIndex('idx_mi_badge_pair')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_mi_badge_pair');
                } else {
                    $this->addSql('DROP INDEX idx_mi_badge_pair ON menu_item_badge');
                }
            }
        }
        if ($schema->hasTable('menu_item_tag')) {
            $table = $schema->getTable('menu_item_tag');
            if ($table->hasIndex('idx_menu_item_tag_menu_item_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_tag_menu_item_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_tag_menu_item_id ON menu_item_tag');
                }
            }
            if ($table->hasIndex('idx_menu_item_tag_tag_id')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_menu_item_tag_tag_id');
                } else {
                    $this->addSql('DROP INDEX idx_menu_item_tag_tag_id ON menu_item_tag');
                }
            }
            if ($table->hasIndex('idx_mi_tag_pair')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_mi_tag_pair');
                } else {
                    $this->addSql('DROP INDEX idx_mi_tag_pair ON menu_item_tag');
                }
            }
        }
        if ($schema->hasTable('order')) {
            $table = $schema->getTable('order');
            if ($table->hasIndex('idx_order_coupon')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_order_coupon');
                } else {
                    $this->addSql('DROP INDEX idx_order_coupon ON `order`');
                }
            }
            if ($table->hasIndex('idx_order_created_at')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_order_created_at');
                } else {
                    $this->addSql('DROP INDEX idx_order_created_at ON `order`');
                }
            }
            if ($table->hasIndex('idx_order_status')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_order_status');
                } else {
                    $this->addSql('DROP INDEX idx_order_status ON `order`');
                }
            }
        }
        if ($schema->hasTable('order_item')) {
            $table = $schema->getTable('order_item');
            if ($table->hasIndex('idx_order_item_menuitem')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_order_item_menuitem');
                } else {
                    $this->addSql('DROP INDEX idx_order_item_menuitem ON order_item');
                }
            }
            if ($table->hasIndex('idx_order_item_order')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_order_item_order');
                } else {
                    $this->addSql('DROP INDEX idx_order_item_order ON order_item');
                }
            }
        }
        if ($schema->hasTable('reservations')) {
            if ($isPostgres) {
                $this->addSql('ALTER TABLE reservations ALTER COLUMN status TYPE VARCHAR(255), ALTER COLUMN status SET DEFAULT \'pending\', ALTER COLUMN status SET NOT NULL');
            } else {
                $this->addSql('ALTER TABLE reservations CHANGE status status VARCHAR(255) DEFAULT \'pending\' NOT NULL');
            }
        }
        if ($schema->hasTable('reviews')) {
            $table = $schema->getTable('reviews');
            if ($table->hasIndex('idx_reviews_created_at')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_reviews_created_at');
                } else {
                    $this->addSql('DROP INDEX idx_reviews_created_at ON reviews');
                }
            }
            if ($table->hasIndex('idx_reviews_is_approved')) {
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_reviews_is_approved');
                } else {
                    $this->addSql('DROP INDEX idx_reviews_is_approved ON reviews');
                }
            }
            // Check if idx_reviews_menu_item exists and IDX_6970EB0F9AB44FE0 doesn't before renaming
            if ($table->hasIndex('idx_reviews_menu_item') && !$table->hasIndex('IDX_6970EB0F9AB44FE0')) {
                if ($isPostgres) {
                    $this->addSql('ALTER INDEX idx_reviews_menu_item RENAME TO IDX_6970EB0F9AB44FE0');
                } else {
                    $this->addSql('ALTER TABLE reviews RENAME INDEX idx_reviews_menu_item TO IDX_6970EB0F9AB44FE0');
                }
            } elseif ($table->hasIndex('idx_reviews_menu_item')) {
                // If both exist, just drop the old one
                if ($isPostgres) {
                    $this->addSql('DROP INDEX IF EXISTS idx_reviews_menu_item');
                } else {
                    $this->addSql('DROP INDEX idx_reviews_menu_item ON reviews');
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contact_msg_replied_by ON contact_message (replied_by_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_gallery_images_category ON gallery_images (category)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_gallery_images_created_at ON gallery_images (created_at)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_gallery_images_is_active ON gallery_images (is_active)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_allergen_allergen_id ON menu_item_allergen (allergen_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_allergen_menu_item_id ON menu_item_allergen (menu_item_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_mi_allergen_pair ON menu_item_allergen (menu_item_id, allergen_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_badge_badge_id ON menu_item_badge (badge_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_badge_menu_item_id ON menu_item_badge (menu_item_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_mi_badge_pair ON menu_item_badge (menu_item_id, badge_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_tag_menu_item_id ON menu_item_tag (menu_item_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_menu_item_tag_tag_id ON menu_item_tag (tag_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_mi_tag_pair ON menu_item_tag (menu_item_id, tag_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_order_coupon ON "order" (coupon_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_order_created_at ON "order" (created_at)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_order_status ON "order" (status)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_order_item_menuitem ON order_item (menu_item_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_order_item_order ON order_item (order_id)');
            $this->addSql('ALTER TABLE reservations ALTER COLUMN status TYPE VARCHAR(20), ALTER COLUMN status DROP DEFAULT, ALTER COLUMN status SET NOT NULL');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_reviews_created_at ON reviews (created_at)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_reviews_is_approved ON reviews (is_approved)');
            $this->addSql('ALTER INDEX IF EXISTS IDX_6970EB0F9AB44FE0 RENAME TO idx_reviews_menu_item');
        } else {
            $this->addSql('CREATE INDEX idx_contact_msg_replied_by ON contact_message (replied_by_id)');
            $this->addSql('CREATE INDEX idx_gallery_images_category ON gallery_images (category)');
            $this->addSql('CREATE INDEX idx_gallery_images_created_at ON gallery_images (created_at)');
            $this->addSql('CREATE INDEX idx_gallery_images_is_active ON gallery_images (is_active)');
            $this->addSql('CREATE INDEX idx_menu_item_allergen_allergen_id ON menu_item_allergen (allergen_id)');
            $this->addSql('CREATE INDEX idx_menu_item_allergen_menu_item_id ON menu_item_allergen (menu_item_id)');
            $this->addSql('CREATE INDEX idx_mi_allergen_pair ON menu_item_allergen (menu_item_id, allergen_id)');
            $this->addSql('CREATE INDEX idx_menu_item_badge_badge_id ON menu_item_badge (badge_id)');
            $this->addSql('CREATE INDEX idx_menu_item_badge_menu_item_id ON menu_item_badge (menu_item_id)');
            $this->addSql('CREATE INDEX idx_mi_badge_pair ON menu_item_badge (menu_item_id, badge_id)');
            $this->addSql('CREATE INDEX idx_menu_item_tag_menu_item_id ON menu_item_tag (menu_item_id)');
            $this->addSql('CREATE INDEX idx_menu_item_tag_tag_id ON menu_item_tag (tag_id)');
            $this->addSql('CREATE INDEX idx_mi_tag_pair ON menu_item_tag (menu_item_id, tag_id)');
            $this->addSql('CREATE INDEX idx_order_coupon ON `order` (coupon_id)');
            $this->addSql('CREATE INDEX idx_order_created_at ON `order` (created_at)');
            $this->addSql('CREATE INDEX idx_order_status ON `order` (status)');
            $this->addSql('CREATE INDEX idx_order_item_menuitem ON order_item (menu_item_id)');
            $this->addSql('CREATE INDEX idx_order_item_order ON order_item (order_id)');
            $this->addSql('ALTER TABLE reservations CHANGE status status VARCHAR(20) NOT NULL');
            $this->addSql('CREATE INDEX idx_reviews_created_at ON reviews (created_at)');
            $this->addSql('CREATE INDEX idx_reviews_is_approved ON reviews (is_approved)');
            $this->addSql('ALTER TABLE reviews RENAME INDEX idx_6970eb0f9ab44fe0 TO idx_reviews_menu_item');
        }
    }
}
