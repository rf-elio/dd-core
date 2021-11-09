<?php declare(strict_types=1);

namespace Elio\FactFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1630056653Export extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1630056653;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_ff_export` (
    `id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT '0',
    `type` VARCHAR(255) NOT NULL,
    `format` VARCHAR(255) NOT NULL,
    `interval` VARCHAR(255) NOT NULL,
    `mapping` json NULL,
    `config` json NULL,
    `last_generation_started_at` DATETIME(3) NULL,
    `last_generation_finished_at` DATETIME(3) NULL,
    `next_generation_due_at` DATETIME(3) NULL,
    `sales_channel_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `base_category_ids` json NULL,
    PRIMARY KEY (`id`),
    KEY `fk.elio_ff_export.sales_channel_id` (`sales_channel_id`),
    KEY `fk.elio_ff_export.language_id` (`language_id`),
    CONSTRAINT `fk.elio_ff_export.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.elio_ff_export.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
