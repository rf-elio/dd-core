<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1737613436SyncProfileDomain extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1737613436;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `elio_data_discovery_sync_profile_domain` (
            `sync_profile_id` BINARY(16) NOT NULL,
            `sales_channel_domain_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`sync_profile_id`,`sales_channel_domain_id`),
            KEY `fk.edd_sync_profile_domain.sync_profile_id` (`sync_profile_id`),
            KEY `fk.edd_sync_profile_domain.sales_channel_domain_id` (`sales_channel_domain_id`),
            CONSTRAINT `fk.edd_sync_profile_domain.sync_profile_id`
                FOREIGN KEY (`sync_profile_id`)
                    REFERENCES `elio_data_discovery_sync_profile` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
            CONSTRAINT `fk.edd_sync_profile_domain.sales_channel_domain_id`
                FOREIGN KEY (`sales_channel_domain_id`)
                    REFERENCES `sales_channel_domain` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeQuery($query);

        $query = <<<SQL
        INSERT INTO `elio_data_discovery_sync_profile_domain` (`sync_profile_id`, `sales_channel_domain_id`)
        SELECT `elio_data_discovery_sync_profile_languages`.`sync_profile_id`,
               MIN(`sales_channel_domain`.`id`) AS `sales_channel_domain_id`
        FROM
            `elio_data_discovery_sync_profile_languages`
        INNER JOIN
            `sales_channel_domain` ON `elio_data_discovery_sync_profile_languages`.`language_id` = `sales_channel_domain`.`language_id`
        GROUP BY
            `elio_data_discovery_sync_profile_languages`.`sync_profile_id`, `elio_data_discovery_sync_profile_languages`.`language_id`;
SQL;

        $connection->executeQuery($query);

        $query = <<<SQL
        DROP TABLE `elio_data_discovery_sync_profile_languages`;
SQL;

        $connection->executeQuery($query);
    }
}
