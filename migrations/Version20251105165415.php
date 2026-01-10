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
        // Drop indexes only if they exist
        if ($schema->hasTable('contact_message') && $schema->getTable('contact_message')->hasIndex('idx_contact_msg_replied_by')) {
            $this->addSql('DROP INDEX idx_contact_msg_replied_by ON contact_message');
        }
        if ($schema->hasTable('gallery_images')) {
            $table = $schema->getTable('gallery_images');
            if ($table->hasIndex('idx_gallery_images_category')) {
                $this->addSql('DROP INDEX idx_gallery_images_category ON gallery_images');
            }
            if ($table->hasIndex('idx_gallery_images_created_at')) {
                $this->addSql('DROP INDEX idx_gallery_images_created_at ON gallery_images');
            }
            if ($table->hasIndex('idx_gallery_images_is_active')) {
                $this->addSql('DROP INDEX idx_gallery_images_is_active ON gallery_images');
            }
        }
        if ($schema->hasTable('menu_item_allergen')) {
            $table = $schema->getTable('menu_item_allergen');
            if ($table->hasIndex('idx_menu_item_allergen_allergen_id')) {
                $this->addSql('DROP INDEX idx_menu_item_allergen_allergen_id ON menu_item_allergen');
            }
            if ($table->hasIndex('idx_menu_item_allergen_menu_item_id')) {
                $this->addSql('DROP INDEX idx_menu_item_allergen_menu_item_id ON menu_item_allergen');
            }
            if ($table->hasIndex('idx_mi_allergen_pair')) {
                $this->addSql('DROP INDEX idx_mi_allergen_pair ON menu_item_allergen');
            }
        }
        if ($schema->hasTable('menu_item_badge')) {
            $table = $schema->getTable('menu_item_badge');
            if ($table->hasIndex('idx_menu_item_badge_badge_id')) {
                $this->addSql('DROP INDEX idx_menu_item_badge_badge_id ON menu_item_badge');
            }
            if ($table->hasIndex('idx_menu_item_badge_menu_item_id')) {
                $this->addSql('DROP INDEX idx_menu_item_badge_menu_item_id ON menu_item_badge');
            }
            if ($table->hasIndex('idx_mi_badge_pair')) {
                $this->addSql('DROP INDEX idx_mi_badge_pair ON menu_item_badge');
            }
        }
        if ($schema->hasTable('menu_item_tag')) {
            $table = $schema->getTable('menu_item_tag');
            if ($table->hasIndex('idx_menu_item_tag_menu_item_id')) {
                $this->addSql('DROP INDEX idx_menu_item_tag_menu_item_id ON menu_item_tag');
            }
            if ($table->hasIndex('idx_menu_item_tag_tag_id')) {
                $this->addSql('DROP INDEX idx_menu_item_tag_tag_id ON menu_item_tag');
            }
            if ($table->hasIndex('idx_mi_tag_pair')) {
                $this->addSql('DROP INDEX idx_mi_tag_pair ON menu_item_tag');
            }
        }
        if ($schema->hasTable('order')) {
            $table = $schema->getTable('order');
            if ($table->hasIndex('idx_order_coupon')) {
                $this->addSql('DROP INDEX idx_order_coupon ON `order`');
            }
            if ($table->hasIndex('idx_order_created_at')) {
                $this->addSql('DROP INDEX idx_order_created_at ON `order`');
            }
            if ($table->hasIndex('idx_order_status')) {
                $this->addSql('DROP INDEX idx_order_status ON `order`');
            }
        }
        if ($schema->hasTable('order_item')) {
            $table = $schema->getTable('order_item');
            if ($table->hasIndex('idx_order_item_menuitem')) {
                $this->addSql('DROP INDEX idx_order_item_menuitem ON order_item');
            }
            if ($table->hasIndex('idx_order_item_order')) {
                $this->addSql('DROP INDEX idx_order_item_order ON order_item');
            }
        }
        if ($schema->hasTable('reservations')) {
            $this->addSql('ALTER TABLE reservations CHANGE status status VARCHAR(255) DEFAULT \'pending\' NOT NULL');
        }
        if ($schema->hasTable('reviews')) {
            $table = $schema->getTable('reviews');
            if ($table->hasIndex('idx_reviews_created_at')) {
                $this->addSql('DROP INDEX idx_reviews_created_at ON reviews');
            }
            if ($table->hasIndex('idx_reviews_is_approved')) {
                $this->addSql('DROP INDEX idx_reviews_is_approved ON reviews');
            }
            // Check if idx_reviews_menu_item exists and IDX_6970EB0F9AB44FE0 doesn't before renaming
            if ($table->hasIndex('idx_reviews_menu_item') && !$table->hasIndex('IDX_6970EB0F9AB44FE0')) {
                $this->addSql('ALTER TABLE reviews RENAME INDEX idx_reviews_menu_item TO IDX_6970EB0F9AB44FE0');
            } elseif ($table->hasIndex('idx_reviews_menu_item')) {
                // If both exist, just drop the old one
                $this->addSql('DROP INDEX idx_reviews_menu_item ON reviews');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
