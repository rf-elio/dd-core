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

namespace Elio\ElioSearch\Core\Sorting;

use ArrayObject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use DomainException;
use Elio\ElioSearch\Configuration\ElioSearchConfigService;
use Elio\ElioSearch\Core\Sorting\Util\CategorySortingUtil;
use Elio\ElioSearch\Core\Sorting\Util\CategoryTreeUtil;
use Elio\ElioSearch\Core\Util\Tree\Node;
use Elio\ElioSearch\Core\Util\Tree\RandomAddTree;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ProductSortingService
 * @package Elio\ElioSearch\Core\Sorting
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductSortingService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $productSortingTreeRepository
    )
    {}

    /**
     * Creates product positions for category
     *
     * @param Context $context
     * @return void
     * @throws Exception
     */
    public function sort(Context $context): void
    {
        $data = $this->prepareSortingData();
        $this->updateProductSortingTree($data, $context);
    }

    /**
     * @param array $data
     * @param Context $context
     * @return void
     */
    private function updateProductSortingTree(array $data, Context $context): void
    {
        $sql = 'SELECT LOWER(HEX(id)) AS id, MD5(LOWER(CONCAT(HEX(product_id), HEX(category_id), position))) AS checksum
                FROM elio_search_product_sorting_tree';
        $rows = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $existingItems = [];
        foreach ($rows as $row) {
            $existingItems[$row['checksum']] = $row['id'];
        }

        $createdData = [];
        $requiredChecksums = [];
        foreach ($data as $item) {
            $checksum = md5($item['productId'].$item['categoryId'].$item['position']);
            $requiredChecksums[] = $checksum;
            if (isset($existingItems[$checksum])) {
                continue;
            }
            $createdData[$checksum] = [
                'id' => Uuid::randomHex(),
                'productId' => $item['productId'],
                'categoryId' => $item['categoryId'],
                'position' => $item['position']
            ];
        }

        $notRequiredChecksums = array_diff(array_keys($existingItems), $requiredChecksums);
        $deleteData = [];
        foreach ($notRequiredChecksums as $notRequiredChecksum) {
            $id = $existingItems[$notRequiredChecksum];
            $deleteData[] = ['id' => $id];
        }

        foreach (array_chunk($deleteData, 500) as $chunk) {
            $this->productSortingTreeRepository->delete($chunk, $context);
        }

        foreach (array_chunk($createdData, 500) as $chunk) {
            $this->productSortingTreeRepository->create($chunk, $context);
        }
    }

    /**
     * Prepares sorting data
     *
     * @return array
     * @throws Exception
     */
    protected function prepareSortingData(): array
    {
        $tree = $this->createCategoryTree();

        $sql = 'SELECT LOWER(HEX(IFNULL(ps.category_id, psParent2.category_id))) as categoryId,
                       LOWER(HEX(p.id)) as productId,
                       COALESCE(ps.position, psParent1.position, psParent2.position) AS position
                FROM product p
                LEFT JOIN product_category pc ON pc.product_id = p.id AND pc.product_version_id = p.version_id
                LEFT JOIN elio_search_product_sorting ps ON ps.product_id = pc.product_id AND ps.product_version_id = pc.product_version_id AND ps.category_id = pc.category_id AND ps.category_version_id = pc.category_version_id
                LEFT JOIN product pParent ON pParent.id = p.parent_id AND pParent.version_id = p.parent_version_id
                LEFT JOIN product_category pcParent ON pcParent.product_id = pParent.id AND pcParent.product_version_id = pParent.version_id
                LEFT JOIN elio_search_product_sorting psParent1 ON psParent1.product_id = p.id AND psParent1.product_version_id = p.version_id AND psParent1.category_id = pcParent.category_id AND psParent1.category_version_id = pcParent.category_version_id
                LEFT JOIN elio_search_product_sorting psParent2 ON psParent2.product_id = pcParent.product_id AND psParent2.product_version_id = pcParent.product_version_id AND psParent2.category_id = pcParent.category_id AND psParent2.category_version_id = pcParent.category_version_id
                ORDER BY position ASC';
        $productCategoryPositions = $this->connection->fetchAllAssociative($sql);
        CategorySortingUtil::addProductSortingToTree($tree, $productCategoryPositions);

        // calculate sorting for parent categories
        $productPositions = new ArrayObject();
        CategorySortingUtil::calculateTreeProductPositions($tree, $productPositions);
        return $productPositions->getArrayCopy();
    }

    /**
     * @return array
     * @throws Exception
     */
    private function createCategoryTree(): array
    {
        // create category tree and sort the nodes
        $categories = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.id)) as categoryId',
                'LOWER(HEX(c.parent_id)) as parentId',
                'LOWER(HEX(c.after_category_id)) as afterCategoryId',
            )
            ->from('category', 'c')
            ->executeQuery()
            ->fetchAllAssociative();

        $tree = new RandomAddTree();
        foreach ($categories as $category) {
            $tree->add($category['categoryId'], $category['parentId'], $category);
        }
        $nodes = $tree->create();
        return CategoryTreeUtil::sortCategoryTree($nodes);
    }

    /**
     * @param string $categoryId
     * @return void
     * @throws Exception
     */
    public function addProducts(string $categoryId): void
    {
        $sortingConfig = $this->systemConfigService->get(ElioSearchConfigService::PLUGIN_CONFIG_PREFIX.'.sortingLocation');

        if ($sortingConfig === 'sortDisabled') {
            return;
        }

        $maxPosition = 0;
        if ($sortingConfig === 'sortEnd') {
            $sql = 'SELECT MAX(position) FROM elio_search_product_sorting WHERE category_id = ?';
            $maxPosition = $this->connection->fetchOne($sql, [Uuid::fromHexToBytes($categoryId)]) ?? 0;
        }

        $sql = 'INSERT INTO elio_search_product_sorting (id, product_id, product_version_id, category_id, category_version_id, position, created_at )
                SELECT UNHEX(MD5(CONCAT(pc.product_id, pc.category_id))) AS id, pc.product_id, pc.product_version_id, pc.category_id, pc.category_version_id, ? + ROW_NUMBER() OVER () AS position, NOW()
                FROM (SELECT @row_number:=0) AS t, product_category AS pc
                LEFT JOIN elio_search_product_sorting esps ON esps.product_id = pc.product_id AND esps.category_id = pc.category_id
                WHERE pc.category_id = ? AND esps.id IS NULL';

        $this->connection->executeStatement($sql, [$maxPosition, Uuid::fromHexToBytes($categoryId)]);

        if ($sortingConfig === 'sortBeginning') {
            $sql = 'CREATE TEMPORARY TABLE temp_existing_entries AS
                    SELECT id,
                           position,
                           (SELECT COUNT(*) FROM elio_search_product_sorting WHERE category_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND)) AS new_entries_count
                    FROM elio_search_product_sorting
                    WHERE category_id = ?
                    AND created_at < DATE_SUB(NOW(), INTERVAL 5 SECOND);
                    
                    UPDATE elio_search_product_sorting AS esps
                    JOIN temp_existing_entries AS existing_entries ON esps.id = existing_entries.id
                    SET esps.position = existing_entries.position + existing_entries.new_entries_count
                    WHERE esps.category_id = ?;
                    
                    DROP TEMPORARY TABLE IF EXISTS temp_existing_entries';

            $this->connection->executeStatement($sql, [
                Uuid::fromHexToBytes($categoryId),
                Uuid::fromHexToBytes($categoryId),
                Uuid::fromHexToBytes($categoryId)
            ]);
        }
    }


    public function removeProducts(): void
    {
        $sql = 'DELETE esps FROM elio_search_product_sorting esps
                LEFT JOIN product_category pc ON pc.product_id = esps.product_id AND esps.category_id = pc.category_id
                WHERE pc.product_id IS NULL';

        $this->connection->executeStatement($sql);
    }
}
