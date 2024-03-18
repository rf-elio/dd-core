<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1694413915SyncProfile extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1694413915;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_data_discovery_sync_profile` (
    `id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT '0',
    `profile` VARCHAR(255) NOT NULL,
    `dataType` VARCHAR(255) NOT NULL,
    `interval` VARCHAR(255) NOT NULL,
    `mapping` json NULL,
    `config` json NULL,
    `last_generation_started_at` DATETIME(3) NULL,
    `last_generation_finished_at` DATETIME(3) NULL,
    `next_generation_due_at` DATETIME(3) NULL,
    `sales_channel_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `base_category_ids` json NULL,
    download_username varchar(255) NULL,
    download_password varchar(255) NULL,
    PRIMARY KEY (`id`),
    KEY `fk.edd_sync_profile.sales_channel_id` (`sales_channel_id`),
    CONSTRAINT `fk.edd_sync_profile.sales_channel_id` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_data_discovery_sync_profile_languages` (
    `sync_profile_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    PRIMARY KEY (`sync_profile_id`,`language_id`),
    KEY `fk.edd_sync_profile_languages.sync_profile_id` (`sync_profile_id`),
    KEY `fk.edd_sync_profile_languages.language_id` (`language_id`),
    CONSTRAINT `fk.edd_sync_profile_languages.sync_profile_id`
        FOREIGN KEY (`sync_profile_id`)
            REFERENCES `elio_data_discovery_sync_profile` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk.edd_sync_profile_languages.language_id`
        FOREIGN KEY (`language_id`)
            REFERENCES `language` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        // TODO: Add new fields to migration
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_data_discovery_entity_status` (
    `id` BINARY(16) NOT NULL,
    `entity_type` VARCHAR(255) NOT NULL,
    `entity_id` BINARY(16) NOT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `data_type` VARCHAR(255) NOT NULL,
    `state` VARCHAR(255) NOT NULL,
    `hash` VARCHAR(255) NOT NULL,
    `deleted_at` DATETIME(3) NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
