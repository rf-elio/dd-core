<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1666196755OrderIndex extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1666196755;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `order` ADD INDEX(`order_date`);
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
