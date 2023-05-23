<?php
declare(strict_types=1);
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\FactFinder\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Class Migration1661932856AddCategoryVersionField
 * @package Elio\FactFinder\Migration
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Migration1661932856AddCategoryVersionField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1661932856;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $queries[] = <<<SQL
ALTER TABLE `elio_ff_filter_restrictions` DROP FOREIGN KEY `fk.elio_ff_filter_restrictions.category_id`;
ALTER TABLE `elio_ff_filter_restrictions` DROP INDEX `fk.elio_ff_filter_restrictions.category_id`;
SQL;

        $queries[] = <<<SQL
ALTER TABLE `elio_ff_filter_restrictions` ADD COLUMN `category_version_id` BINARY(16) NULL AFTER `category_id`;
SQL;

        $queries[] = 'UPDATE `elio_ff_filter_restrictions` SET `category_version_id` = UNHEX(\'' . Defaults::LIVE_VERSION . '\') WHERE `category_id` IS NOT NULL;';

        $queries[] = <<<SQL
ALTER TABLE `elio_ff_filter_restrictions` ADD
    KEY `fk.elio_ff_filter_restrictions.category_id` (`category_id`,`category_version_id`);
ALTER TABLE `elio_ff_filter_restrictions` ADD
    CONSTRAINT `fk.elio_ff_filter_restrictions.category_id`
        FOREIGN KEY (`category_id`,`category_version_id`)
            REFERENCES `category` (`id`,`version_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE;
SQL;

        foreach ($queries as $query) {
            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
