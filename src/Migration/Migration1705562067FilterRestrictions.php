<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1705562067FilterRestrictions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1705562067;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `elio_data_discovery_filter`
    ADD `type` VARCHAR(255) NULL AFTER `technical_name`,
    ADD `displayed_by_default` TINYINT(1) DEFAULT '0' AFTER `is_custom`;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
UPDATE `elio_data_discovery_filter` SET `type` = 'filter' WHERE `type` IS NULL;
SQL;
        $connection->executeStatement($query);

        $query = <<<SQL
ALTER TABLE `elio_data_discovery_filter`
    MODIFY `type` VARCHAR(255) NOT NULL;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
