<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1747923125EntityStatusIndex extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1747923125;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE INDEX `idx.entity_type_identifier_sales_channel_id` ON `elio_data_discovery_entity_status` (`entity_type`, `identifier`, `sales_channel_id`);
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
