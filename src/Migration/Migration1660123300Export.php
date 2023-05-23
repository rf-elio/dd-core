<?php declare(strict_types=1);

namespace Elio\FactFinder\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1660123300Export extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1660123300;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE elio_ff_export ADD download_username varchar(255) NULL;
ALTER TABLE elio_ff_export ADD download_password varchar(255) NULL;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
