<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260118112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deduplicate tags and badges, enforce unique constraints';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            // Deduplicate tags by code
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY code) AS keep_id
    FROM tag
)
DELETE FROM menu_item_tag mit
USING dupes d, menu_item_tag mit2
WHERE mit.tag_id = d.dup_id
  AND d.dup_id <> d.keep_id
  AND mit2.menu_item_id = mit.menu_item_id
  AND mit2.tag_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY code) AS keep_id
    FROM tag
)
UPDATE menu_item_tag mit
SET tag_id = d.keep_id
FROM dupes d
WHERE mit.tag_id = d.dup_id
  AND d.dup_id <> d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY code) AS keep_id
    FROM tag
)
DELETE FROM tag t
USING dupes d
WHERE t.id = d.dup_id
  AND d.dup_id <> d.keep_id;
SQL);

            // Deduplicate badges by name (case-insensitive)
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY LOWER(name)) AS keep_id
    FROM badge
)
DELETE FROM menu_item_badge mib
USING dupes d, menu_item_badge mib2
WHERE mib.badge_id = d.dup_id
  AND d.dup_id <> d.keep_id
  AND mib2.menu_item_id = mib.menu_item_id
  AND mib2.badge_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY LOWER(name)) AS keep_id
    FROM badge
)
UPDATE menu_item_badge mib
SET badge_id = d.keep_id
FROM dupes d
WHERE mib.badge_id = d.dup_id
  AND d.dup_id <> d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
WITH dupes AS (
    SELECT id AS dup_id, MIN(id) OVER (PARTITION BY LOWER(name)) AS keep_id
    FROM badge
)
DELETE FROM badge b
USING dupes d
WHERE b.id = d.dup_id
  AND d.dup_id <> d.keep_id;
SQL);

            // Enforce unique constraints
            $this->addSql('CREATE UNIQUE INDEX uniq_tag_code ON tag (code)');
            $this->addSql('CREATE UNIQUE INDEX uniq_badge_name ON badge (name)');
        } else {
            // MySQL / MariaDB
            $this->addSql(<<<'SQL'
CREATE TEMPORARY TABLE tmp_tag_dedupe AS
SELECT t1.id AS dup_id, t2.min_id AS keep_id
FROM tag t1
JOIN (
    SELECT code, MIN(id) AS min_id
    FROM tag
    GROUP BY code
    HAVING COUNT(*) > 1
) t2 ON t1.code = t2.code AND t1.id <> t2.min_id;
SQL);
            $this->addSql(<<<'SQL'
DELETE mit
FROM menu_item_tag mit
JOIN tmp_tag_dedupe d ON mit.tag_id = d.dup_id
JOIN menu_item_tag mit2 ON mit2.menu_item_id = mit.menu_item_id AND mit2.tag_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
UPDATE menu_item_tag mit
JOIN tmp_tag_dedupe d ON mit.tag_id = d.dup_id
SET mit.tag_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
DELETE t
FROM tag t
JOIN tmp_tag_dedupe d ON t.id = d.dup_id;
SQL);
            $this->addSql('DROP TEMPORARY TABLE tmp_tag_dedupe;');

            $this->addSql(<<<'SQL'
CREATE TEMPORARY TABLE tmp_badge_dedupe AS
SELECT b1.id AS dup_id, b2.min_id AS keep_id
FROM badge b1
JOIN (
    SELECT name, MIN(id) AS min_id
    FROM badge
    GROUP BY name
    HAVING COUNT(*) > 1
) b2 ON b1.name = b2.name AND b1.id <> b2.min_id;
SQL);
            $this->addSql(<<<'SQL'
DELETE mib
FROM menu_item_badge mib
JOIN tmp_badge_dedupe d ON mib.badge_id = d.dup_id
JOIN menu_item_badge mib2 ON mib2.menu_item_id = mib.menu_item_id AND mib2.badge_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
UPDATE menu_item_badge mib
JOIN tmp_badge_dedupe d ON mib.badge_id = d.dup_id
SET mib.badge_id = d.keep_id;
SQL);
            $this->addSql(<<<'SQL'
DELETE b
FROM badge b
JOIN tmp_badge_dedupe d ON b.id = d.dup_id;
SQL);
            $this->addSql('DROP TEMPORARY TABLE tmp_badge_dedupe;');

            $this->addSql('ALTER TABLE tag ADD UNIQUE INDEX uniq_tag_code (code)');
            $this->addSql('ALTER TABLE badge ADD UNIQUE INDEX uniq_badge_name (name)');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $isPostgres = $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform;

        if ($isPostgres) {
            $this->addSql('DROP INDEX IF EXISTS uniq_tag_code');
            $this->addSql('DROP INDEX IF EXISTS uniq_badge_name');
        } else {
            $this->addSql('ALTER TABLE tag DROP INDEX uniq_tag_code');
            $this->addSql('ALTER TABLE badge DROP INDEX uniq_badge_name');
        }
    }
}
