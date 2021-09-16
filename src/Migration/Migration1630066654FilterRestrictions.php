<?php
declare(strict_types=1);

namespace Elio\FactFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1630066654FilterRestrictions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1630066654;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_ff_filter` (
    `id` BINARY(16) NOT NULL,
    `property_name` VARCHAR(255) NOT NULL,
    `is_custom` TINYINT(1) DEFAULT '0',
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_ff_filter_restrictions` (
    `id` BINARY(16) NOT NULL,
    `is_category` TINYINT(1) NULL DEFAULT '0',
    `layer` VARCHAR(255) NULL,
    `is_allowed` TINYINT(1) NULL DEFAULT '0',
    `category_id` BINARY(16) NULL DEFAULT NULL,
    `is_all_checked` TINYINT(1) NULL DEFAULT '0',
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_ff_filter_restrictions_filters` (
    `filter_restriction_id` BINARY(16) NOT NULL,
    `filter_id` BINARY(16) NOT NULL,
    PRIMARY KEY (`filter_restriction_id`,`filter_id`),
    KEY `fk.elio_ff_filter_restrictions_filters.filter_restriction_id` (`filter_restriction_id`),
    KEY `fk.elio_ff_filter_restrictions_filters.filter_id` (`filter_id`),
    CONSTRAINT `fk.elio_ff_filter_restrictions_filters.filter_restriction_id`
        FOREIGN KEY (`filter_restriction_id`)
            REFERENCES `elio_ff_filter_restrictions` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT `fk.elio_ff_filter_restrictions_filters.filter_id`
        FOREIGN KEY (`filter_id`)
            REFERENCES `elio_ff_filter` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
