<?php
declare(strict_types=1);

namespace Elio\FactFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1636581600FilterRestrictions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1636581600;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
alter table `elio_ff_filter`
    add `technical_name` varchar(255) not null after `id`;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
alter table `elio_ff_filter_restrictions`
    add `language_id` BINARY(16) NULL DEFAULT NULL after `sales_channel_id`;
SQL;
        $connection->executeStatement($query);

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}