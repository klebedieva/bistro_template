<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250926000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add prep_time_min and prep_time_max to menu_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu_item ADD prep_time_min INT DEFAULT NULL, ADD prep_time_max INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu_item DROP prep_time_min, DROP prep_time_max');
    }
}


