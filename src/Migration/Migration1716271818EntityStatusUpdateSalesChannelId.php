<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1716271818EntityStatusUpdateSalesChannelId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1716271818;
    }


    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $query = <<<SQL
SELECT sales_channel_id FROM elio_data_discovery_sync_profile
SQL;

        $salesChannelId = $connection->fetchOne($query);
        if (empty($salesChannelId)) {
            return;
        }

        $query = <<<SQL
UPDATE `elio_data_discovery_entity_status` SET `sales_channel_id` = ? WHERE sales_channel_id IS NULL
SQL;

        $connection->executeStatement($query, [$salesChannelId]);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
