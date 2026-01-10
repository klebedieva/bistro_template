<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109190826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_item DROP prep_time_minutes');
        $this->addSql('ALTER TABLE `order` ADD client_first_name VARCHAR(255) DEFAULT NULL, ADD client_last_name VARCHAR(255) DEFAULT NULL, ADD client_phone VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_item ADD prep_time_minutes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` DROP client_first_name, DROP client_last_name, DROP client_phone');
    }
}
