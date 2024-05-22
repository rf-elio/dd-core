<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1716271817EntityStatusUpdateSalesChannelId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1716271817;
    }


    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $query = <<<SQL
UPDATE `elio_data_discovery_entity_status` es
    JOIN `elio_data_discovery_sync_profile` sp
    ON (es.`entity_type` = 'product' AND sp.`dataType` = 'Elio\\ElioDataDiscovery\\Core\\Sync\\DataTypes\\ProductDataType')
       OR (es.`entity_type` IN ('category', 'landing_page') AND sp.`dataType` = 'Elio\\ElioDataDiscovery\\Core\\Sync\\DataTypes\\ContentDataType')
    SET es.`sales_channel_id` = sp.`sales_channel_id`
    WHERE es.`entity_type` IN ('product', 'category', 'landing_page');
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
