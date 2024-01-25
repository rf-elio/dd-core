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
use Doctrine\DBAL\Exception;
use DomainException;
use Elio\ElioSearch\Configuration\ElioSearchConfigService;
use Elio\ElioSearch\Core\Util\Tree\Node;
use Elio\ElioSearch\Core\Util\Tree\RandomAddTree;
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
    )
    {
    }

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
        $productSortingIds = $this->productSortingRepository->searchIds(new Criteria(), $context)->getIds();
        foreach (array_chunk($productSortingIds, 500) as $chunk) {
            $this->productSortingRepository->delete(array_map(static function ($productSortingId) {
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
            $this->productSortingRepository->create($chunk, $context);
        }
    }

    /**
     * Changes position for product in category and recalculates product position in all parent categories
     *
     * @param int $position
     * @param string $categoryId
     * @param string $productId
     * @param Context $context
     * @return void
     * @throws Exception
     */
    public function changePosition(int $position, string $categoryId, string $productId, Context $context): void
    {
        $categoriesSortingData = $this->getCategoriesSortingData();
        $sortingData = [];
        $currentProductPositions = [];
        foreach ($categoriesSortingData as $item) {
            $currentProductPositions[$item['id'] . '_' . $item['productId']] = [
                'productSortingId' => $item['productSortingId'],
                'position' => (int)$item['position']
            ];

            if ($item['id'] === $categoryId) {
                $sortingData[] = $item;
            }
        }

        if (empty($sortingData)) {
            throw new DomainException('Category data is not set for provided category');
        }

        $productKey = array_search($productId, array_column($sortingData, 'productId'));
        $currentPosition = $sortingData[$productKey]['position'];

        $updatedData = [];
        foreach ($sortingData as $key => $productPosition) {
            if ($key === $productKey) {
                $updatedData[] = [
                    'id' => $productPosition['productSortingId'],
                    'position' => $position
                ];
                continue;
            }

            if ($position > $currentPosition && $productPosition['position'] <= $position) {
                $updatedData[] = [
                    'id' => $productPosition['productSortingId'],
                    'position' => $productPosition['position'] - 1
                ];
                continue;
            }

            if ($position < $currentPosition && $productPosition['position'] < $currentPosition) {
                $updatedData[] = [
                    'id' => $productPosition['productSortingId'],
                    'position' => $productPosition['position'] + 1
                ];
            }
        }

        $this->productSortingRepository->update($updatedData, $context);

        $categoriesSortingData = $this->getCategoriesSortingData();
        $productSortPositions = $this->generateProductSortPositionsForCategory($categoriesSortingData);
        $isMergeSortingStrategy = $this->configService->get(ElioSearchConfigService::PLUGIN_CONFIG_PREFIX . '.mergeSortingStrategy') ?? false;
        $updatedData = [];
        foreach ($productSortPositions as $categoryId => $productPosition) {
            if ($isMergeSortingStrategy) {
                $sorted = $this->calculateSortingByMergeStrategy($productPosition);
            } else {
                $sorted = $this->calculateSortingByCategoryStrategy($productPosition);
            }

            foreach ($sorted as $item) {
                if ($currentProductPositions[$categoryId . '_' . $item['productId']]['position'] !== $item['position']) {
                    $updatedData[] = [
                        'id' => $currentProductPositions[$categoryId . '_' . $item['productId']]['productSortingId'],
                        'position' => $item['position']
                    ];
                }
            }
        }

        foreach (array_chunk($updatedData, 500) as $chunk) {
            $this->productSortingRepository->update($chunk, $context);
        }
    }

    /**
     * Processes product sort for provided category tree
     *
     * @param Node $node
     * @param \ArrayObject $arrayObject
     * @param bool $sortChild
     * @return array
     */
    protected function processProductSort(Node $node, \ArrayObject $arrayObject, bool $sortChild = false): array
    {
        if (empty($node->getChildNodes())) {
            if ($sortChild) {
                $arr = $node->getValue()['products'];
                usort($arr, static function ($a, $b) {
                    return $a['position'] <=> $b['position'];
                });

                $arrayObject[$node->getId()] = $arr;
            }

            return $node->getValue()['products'];
        }

        $arr = [];
        foreach ($node->getChildNodes() as $childNode) {
            $productPositions = $this->processProductSort($childNode, $arrayObject, $sortChild);
            foreach ($productPositions as $productPosition) {
                $arr[] = $productPosition;
            }
        }

        usort($arr, static function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        $arrayObject[$node->getId()] = $arr;
        return $arr;
    }

    /**
     * Prepares sorting data
     *
     * @return array
     * @throws Exception
     */
    protected function prepareSortingData(): array
    {
        $categorySortingData = $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.id)) as id',
                'LOWER(HEX(c.parent_id)) as parentId',
                'LOWER(HEX(pct.product_id)) as productId',
                'LOWER(HEX(pct.product_version_id)) as productVersionId'
            )
            ->from('category', 'c')
            ->leftJoin('c', 'product_category', 'pct', 'c.id = pct.category_id')
            ->executeQuery()
            ->fetchAllAssociative();

        $productSortPositions = $this->generateProductSortPositionsForCategory($categorySortingData, true);
        $sorting = [];
        $isMergeSortingStrategy = $this->configService->get(ElioSearchConfigService::PLUGIN_CONFIG_PREFIX . '.mergeSortingStrategy') ?? false;
        foreach ($productSortPositions as $categoryId => $productPosition) {
            if ($isMergeSortingStrategy) {
                $sorted = $this->calculateSortingByMergeStrategy($productPosition);
            } else {
                $sorted = $this->calculateSortingByCategoryStrategy($productPosition);
            }
            foreach ($sorted as $item) {
                $sorting[] = [
                    'categoryId' => $categoryId,
                    'productId' => $item['productId'],
                    'position' => $item['position'],
                ];
            }
        }

        return $sorting;
    }

    /**
     * Generates an array with product position for category
     *
     * @param array $categorySortingData
     * @param bool $sortChild
     * @return array
     */
    protected function generateProductSortPositionsForCategory(array $categorySortingData, bool $sortChild = false): array
    {
        $categories = $this->prepareCategories($categorySortingData);

        $tree = new RandomAddTree();
        foreach ($categories as $category) {
            $tree->add($category['id'], $category['parentId'], $category);
        }

        $nodes = $tree->create();

        $productSortPositions = new \ArrayObject();
        foreach ($nodes as $node) {
            $this->processProductSort($node, $productSortPositions, $sortChild);
        }

        $result = [];
        foreach ($productSortPositions as $id => $productPositions) {
            foreach ($productPositions as $productPosition) {
                $result[$id][$productPosition['position']][] = [
                    'productId' => $productPosition['id'],
                    'categoryId' => $productPosition['categoryId']
                ];
            }
        }

        return $result;
    }

    /**
     * Gets category data with product positions
     *
     * @return array
     * @throws Exception
     */
    protected function getCategoriesSortingData(): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                'LOWER(HEX(c.id)) as id',
                'LOWER(HEX(c.parent_id)) as parentId',
                'LOWER(HEX(esps.id)) as productSortingId',
                'LOWER(HEX(esps.product_id)) as productId',
                'esps.position'
            )
            ->from('category', 'c')
            ->innerJoin('c', 'elio_search_product_sorting', 'esps', 'esps.category_id = c.id')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Calculates sorting for current product positions using category strategy
     *
     * @param array $productPositions
     * @return array
     */
    protected function calculateSortingByMergeStrategy(array $productPositions): array
    {
        $sorting = [];
        $position = 1;
        foreach ($productPositions as $productPosition) {
            foreach ($productPosition as $item) {
                $sorting[] = [
                    'productId' => $item['productId'],
                    'position' => $position
                ];

                $position++;
            }
        }

        return $sorting;
    }

    /**
     * Calculates sorting for current product positions using category strategy
     *
     * @param array $productPositions
     * @return array
     */
    protected function calculateSortingByCategoryStrategy(array $productPositions): array
    {
        $resorted = [];
        foreach ($productPositions as $position => $productPosition) {
            foreach ($productPosition as $item) {
                $resorted[$item['categoryId']][] = [
                    'productId' => $item['productId'],
                    'position' => $position
                ];
            }
        }

        $sorting = [];
        $position = 1;
        foreach ($resorted as $items) {
            foreach ($items as $item) {
                $sorting[] = [
                    'productId' => $item['productId'],
                    'position' => $position
                ];

                $position++;
            }
        }

        return $sorting;
    }

    /**
     * Prepares category data with product positions in it
     *
     * @param array $categoriesSortingData
     * @return array
     */
    protected function prepareCategories(array $categoriesSortingData): array
    {
        $categories = [];
        foreach ($categoriesSortingData as $item) {
            if (isset($categories[$item['id']])) {
                $categories[$item['id']]['products'][] = [
                    'id' => $item['productId'],
                    'categoryId' => $item['id'],
                    'productSortingId' => $item['productSortingId'] ?? null,
                    'position' => $item['position'] ?? count($categories[$item['id']]['products']) + 1
                ];

                continue;
            }

            $categories[$item['id']] = [
                'id' => $item['id'],
                'parentId' => $item['parentId'],
                'products' => [
                    [
                        'id' => $item['productId'],
                        'categoryId' => $item['id'],
                        'productSortingId' => $item['productSortingId'] ?? null,
                        'position' => $item['position'] ?? 1
                    ]
                ]
            ];
        }

        return array_values($categories);
    }
}