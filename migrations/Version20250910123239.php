<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910123239 extends AbstractMigration
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
            $this->addSql('ALTER TABLE contact_message ADD replied_by_id INT DEFAULT NULL, ADD is_replied BOOLEAN NOT NULL, ADD replied_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ADD reply_message TEXT DEFAULT NULL');
            $this->addSql('ALTER TABLE contact_message ADD CONSTRAINT FK_2C9211FED6FBBEB5 FOREIGN KEY (replied_by_id) REFERENCES users (id)');
            $this->addSql('CREATE INDEX IDX_2C9211FED6FBBEB5 ON contact_message (replied_by_id)');
            $this->addSql('ALTER TABLE reviews DROP consent');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN name TYPE VARCHAR(255), ALTER COLUMN name SET NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN email TYPE VARCHAR(255), ALTER COLUMN email DROP NOT NULL, ALTER COLUMN email SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE contact_message ADD replied_by_id INT DEFAULT NULL, ADD is_replied TINYINT(1) NOT NULL, ADD replied_at DATETIME DEFAULT NULL, ADD reply_message LONGTEXT DEFAULT NULL');
            $this->addSql('ALTER TABLE contact_message ADD CONSTRAINT FK_2C9211FED6FBBEB5 FOREIGN KEY (replied_by_id) REFERENCES users (id)');
            $this->addSql('CREATE INDEX IDX_2C9211FED6FBBEB5 ON contact_message (replied_by_id)');
            $this->addSql('ALTER TABLE reviews DROP consent, CHANGE name name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('ALTER TABLE contact_message DROP CONSTRAINT FK_2C9211FED6FBBEB5');
            $this->addSql('DROP INDEX IF EXISTS IDX_2C9211FED6FBBEB5');
            $this->addSql('ALTER TABLE contact_message DROP replied_by_id, DROP is_replied, DROP replied_at, DROP reply_message');
            $this->addSql('ALTER TABLE reviews ADD consent BOOLEAN NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN name TYPE VARCHAR(80), ALTER COLUMN name SET NOT NULL');
            $this->addSql('ALTER TABLE reviews ALTER COLUMN email TYPE VARCHAR(180), ALTER COLUMN email DROP NOT NULL, ALTER COLUMN email SET DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE contact_message DROP FOREIGN KEY FK_2C9211FED6FBBEB5');
            $this->addSql('DROP INDEX IDX_2C9211FED6FBBEB5 ON contact_message');
            $this->addSql('ALTER TABLE contact_message DROP replied_by_id, DROP is_replied, DROP replied_at, DROP reply_message');
            $this->addSql('ALTER TABLE reviews ADD consent TINYINT(1) NOT NULL, CHANGE name name VARCHAR(80) NOT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL');
        }
    }
}
