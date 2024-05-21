<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1715924804IndexerSalesChannel extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1715924804;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `elio_data_discovery_entity_status`
ADD COLUMN `sales_channel_id` BINARY(16) NULL AFTER `identifier`;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
