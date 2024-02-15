<?php declare(strict_types=1);

namespace Elio\ElioSearch\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1708004996FilterRestrictionUniqueKey extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1708004996;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `elio_search_filter`
ADD CONSTRAINT uc_filter UNIQUE (technical_name, type)
SQL;

        $connection->executeStatement($query);

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
