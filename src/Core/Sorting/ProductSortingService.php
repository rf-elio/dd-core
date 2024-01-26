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
        $productSortingIds = $this->productSortingTreeRepository->searchIds(new Criteria(), $context)->getIds();
        foreach (array_chunk($productSortingIds, 500) as $chunk) {
            $this->productSortingTreeRepository->delete(array_map(static function ($productSortingId) {
                return ['id' => $productSortingId];
            }, $chunk), $context);
        }

        $createdData = [];
        foreach ($data as $item) {
            $createdData[] = [
                'id' => Uuid::randomHex(),
                'productId' => $item['productId'],
                'categoryId' => $item['categoryId'],
                'position' => $item['position']
            ];
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

        $tree = $this->createCategoryTree($categories);
        $tree = CategorySortingUtil::sortCategoryTree($tree);

        // add product positions to tree
        $productCategoryPositions = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.category_id)) as categoryId',
                'LOWER(HEX(c.product_id)) as productId',
                's.position'
            )
            ->from('product_category', 'c')
            ->leftJoin('c', 'elio_search_product_sorting', 's', 's.category_id = c.category_id AND s.product_id = c.product_id')
            ->orderBy('s.position', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
        CategorySortingUtil::addProductSortingToTree($tree, $productCategoryPositions);

        // calculate sorting for parent categories
        $productPositions = new ArrayObject();
        CategorySortingUtil::calculateTreeProductPositions($tree, $productPositions);
        return $productPositions->getArrayCopy();
    }

    /**
     * Creates a category tree based on the given categories.
     *
     * @param array $categories The array of categories.
     *                          Each category should have keys:
     *                          - 'categoryId' (string): The ID of the category.
     *                          - 'parentId' (string): The ID of the parent category.
     *                          - Other attributes specific to the category.
     * @return array The created category tree.
     * @throws Exception If there is an error creating the tree.
     */
    private function createCategoryTree(array $categories): array
    {
        $tree = new RandomAddTree();
        foreach ($categories as $category) {
            $tree->add($category['categoryId'], $category['parentId'], $category);
        }
        return $tree->create();
    }
}