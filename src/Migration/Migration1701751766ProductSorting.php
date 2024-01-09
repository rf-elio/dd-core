<?php declare(strict_types=1);

namespace Elio\ElioSearch\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1701751766ProductSorting extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1701751766;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `elio_search_product_sorting` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `category_id` BINARY(16) NOT NULL,
    `category_version_id` BINARY(16) NOT NULL,
    `position` INT(11) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    KEY `fk.elio_search_product_sorting.product_id` (`product_id`,`product_version_id`),
    KEY `fk.elio_search_product_sorting.category_id` (`category_id`,`category_version_id`),
    CONSTRAINT `fk.elio_search_product_sorting.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.elio_search_product_sorting.category_id` FOREIGN KEY (`category_id`,`category_version_id`) REFERENCES `category` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
