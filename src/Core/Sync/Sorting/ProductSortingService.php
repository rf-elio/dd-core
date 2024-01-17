<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioSearch\Core\Sync\Sorting;

use Doctrine\DBAL\Connection;
use Elio\ElioSearch\Configuration\ElioSearchConfigService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ProductSortingService
 * @package Elio\ElioSearch\Core\Sync\Sorting
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductSortingService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SystemConfigService $configService,
        private readonly EntityRepository $productSortingRepository
    ) {
    }

    /**
     * Creates product positions for category
     *
     * @param Context $context
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function sort(Context $context): void
    {
        $isMergeSortingStrategy = $this->configService->get(ElioSearchConfigService::PLUGIN_CONFIG_PREFIX . '.mergeSortingStrategy') ?? false;
        if ($isMergeSortingStrategy) {
            $data = $this->prepareSortByMergeStrategyData();
        } else {
            $data = $this->prepareSortByCategoryStrategy();
        }

        $productSortingIds = $this->productSortingRepository->searchIds(new Criteria(), $context)->getIds();
        foreach (array_chunk($productSortingIds, 500) as $chunk) {
            $this->productSortingRepository->delete(array_map(static function ($productSortingId) {
                return ['id' => $productSortingId];
            }, $chunk), $context);
        }

        $createdData = [];
        foreach ($data as $categorySortingData) {
            foreach ($categorySortingData as $item) {
                $createdData[] = [
                    'id' => Uuid::randomHex(),
                    'productId' => $item['productId'],
                    'productVersionId' => $item['productVersionId'],
                    'categoryId' => $item['categoryId'],
                    'position' => $item['position']
                ];
            }
        }

        foreach (array_chunk($createdData, 500) as $chunk) {
            $this->productSortingRepository->create($chunk, $context);
        }
    }

    /**
     * Prepares sorting data for merge
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function prepareSortByMergeStrategyData(): array
    {
        $result = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.id)) as id',
                'LOWER(HEX(c.parent_id)) as parent_id',
                'c.child_count',
                'LOWER(HEX(p.id)) as product_id',
                'LOWER(HEX(p.version_id)) as product_version_id',
                'p.category_ids'
            )
            ->from('category', 'c')
            ->leftJoin('c', 'product_category_tree', 'pct', 'c.id = pct.category_id')
            ->leftJoin('pct', 'product', 'p', 'p.id = pct.product_id and p.version_id = pct.product_version_id')
            ->orderBy('c.level', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        $sorting = [];
        $offset = [];
        foreach ($result as $row) {
            $position = 1;
            if (isset($sorting[$row['id']])) {
                $position = count($sorting[$row['id']]) + 1;
            }

            if (!in_array($row['id'], json_decode($row['category_ids'], false), true)) {
                if (isset($offset[$row['id']])) {
                    $offset[$row['id']] += $row['child_count'];
                    $position = $offset[$row['id']];
                } else {
                    $offset[$row['id']] = $position;
                }
            }

            $sorting[$row['id']][] = [
                'categoryId' => $row['id'],
                'productId' => $row['product_id'],
                'productVersionId' => $row['product_version_id'],
                'position' => $position,
            ];
        }

        return $sorting;
    }

    /**
     * Prepares sorting data for category strategy
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function prepareSortByCategoryStrategy(): array
    {
        $result = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.id)) as id',
                'LOWER(HEX(p.id)) as product_id',
                'LOWER(HEX(p.version_id)) as product_version_id'
            )
            ->from('category', 'c')
            ->leftJoin('c', 'product_category_tree', 'pct', 'c.id = pct.category_id')
            ->leftJoin('pct', 'product', 'p', 'p.id = pct.product_id and p.version_id = pct.product_version_id')
            ->orderBy('c.level', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        $sorting = [];
        foreach ($result as $row) {
            $position = 1;
            if (isset($sorting[$row['id']])) {
                $position = count($sorting[$row['id']]) + 1;
            }

            $sorting[$row['id']][] = [
                'categoryId' => $row['id'],
                'productId' => $row['product_id'],
                'productVersionId' => $row['product_version_id'],
                'position' => $position,
            ];
        }

        return $sorting;
    }
}