<?php
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

namespace Elio\ElioSearch\Core\FilterRestrictions\Setup;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Elio\ElioSearch\Core\FilterRestrictions\Aggregate\FilterDefinitionTranslation\FilterDefinitionTranslationDefinition;
use Elio\ElioSearch\Core\FilterRestrictions\FilterDefinition;
use Elio\ElioSearch\Core\FilterRestrictions\FilterRestrictionsDefinition;
use Elio\ElioSearch\Core\FilterRestrictions\FilterRestrictionsFilterMapping;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class FilterRestrictionsSetup
 *
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterRestrictionsSetup
{
    private ?EntityRepository $filterRepository = null;
    private ?Connection $connection = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    )
    {
        try {
            $filterRepository = $container->get('elio_search_filter.repository');
            if ($filterRepository instanceof EntityRepository) {
                $this->filterRepository = $filterRepository;
            }
            $connection = $container->get(Connection::class);
            if ($connection instanceof Connection) {
                $this->connection = $connection;
            }
        } catch (ServiceNotFoundException) {
        }
    }

    /**
     * @param Context $context
     * @param array|null $filters
     * @param bool $skipIfExists
     */
    public function createFilters(Context $context, ?array $filters = null, bool $skipIfExists = false): void
    {
        if ($this->filterRepository === null) {
            return;
        }

        if ($skipIfExists && $this->filterRepository->searchIds(new Criteria(), $context)->getTotal() > 0) {
            return;
        }

        foreach ($filters ?? [] as $filterName) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('technicalName', $filterName));
            $criteria->addFilter(new EqualsFilter('isCustom', true));

            if ($this->filterRepository->searchIds($criteria, $context)->getTotal() > 0) {
                continue;
            }

            $newFilterId = Uuid::randomHex();
            $this->filterRepository->create(
                [
                    [
                        'id' => $newFilterId,
                        'propertyName' => $filterName,
                        'technicalName' => $filterName,
                        'type' => 'filter',
                        'isCustom' => true
                    ]
                ],
                $context
            );
        }
    }

    /**
     * @throws Exception
     */
    public function removeTables(): void
    {
        if ($this->connection === null) {
            return;
        }
        $tables = [
            FilterDefinitionTranslationDefinition::ENTITY_NAME,
            FilterRestrictionsFilterMapping::ENTITY_NAME,
            FilterRestrictionsDefinition::ENTITY_NAME,
            FilterDefinition::ENTITY_NAME
        ];
        foreach ($tables as $table) {
            $this->connection->executeStatement(sprintf('DROP TABLE IF EXISTS `%s`', $table));
        }
    }
}
