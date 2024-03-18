<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Sorting\Util;


use ArrayObject;
use Elio\ElioDataDiscovery\Core\Util\Tree\Node;

/**
 * Class CategorySortingUtil
 * @package Elio\ElioDataDiscovery\Core\Sorting\Util
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class CategorySortingUtil
{
    /**
     * Adds the product sort positions into the tree
     *
     * @param array $tree
     * @param array $productCategoryPositions
     * @return void
     */
    public static function addProductSortingToTree(array $tree, array $productCategoryPositions): void
    {
        // group positions by category ids
        $groupedProductCategoryPositions = [];
        foreach ($productCategoryPositions as $productCategoryPosition) {
            $categoryId = $productCategoryPosition['categoryId'];
            $productId = $productCategoryPosition['productId'];
            $position = $productCategoryPosition['position'];

            if (!isset($groupedProductCategoryPositions[$categoryId])) {
                $groupedProductCategoryPositions[$categoryId] = [];
            }
            $groupedProductCategoryPositions[$categoryId][$productId] = $position;
        }

        // add product sort positions to matching category nodes
        self::addProductSorting($tree, $groupedProductCategoryPositions);
    }

    /**
     * @param Node[] $nodes
     * @param array $groupedProductCategoryPositions
     * @return void
     */
    private static function addProductSorting(array $nodes, array $groupedProductCategoryPositions): void
    {
        foreach ($nodes as $node) {
            self::addProductSorting($node->getChildNodes(), $groupedProductCategoryPositions);

            $value = $node->getValue();
            $categoryId = $value['categoryId'];

            if (!isset($groupedProductCategoryPositions[$categoryId])) {
                continue;
            }

            $products = $groupedProductCategoryPositions[$categoryId];


            $maxPosition = count($products);
            $normalizedPosition = 0;
            asort($products);
            foreach ($products as $productId => $position) {
                if ($position === null) {
                    $products[$productId] = ++$maxPosition;
                } else {
                    $products[$productId] = $normalizedPosition++;
                }
            }

            asort($products);
            $value['products'] = $products;
            $node->setValue($value);
        }
    }

    /**
     * @param Node[] $tree
     * @param ArrayObject $productPositions
     * @return array
     */
    public static function calculateTreeProductPositions(array $tree, ArrayObject $productPositions): array
    {
        $productPositionsPos = 0;
        $currentCategoryProducts = [];

        foreach ($tree as $node) {
            $childCategoryProducts = self::calculateTreeProductPositions($node->getChildNodes(), $productPositions);

            if (!empty($node->getChildNodes())) {
                $value = $node->getValue();
                $value['products'] = $childCategoryProducts;
                $node->setValue($value);
            }

            $childProductPositions = self::addNodeProductPositions($node, $productPositions);
            foreach ($childProductPositions as $productId => $position) {
                $currentCategoryProducts[$productId] = $productPositionsPos++;
            }
        }

        return $currentCategoryProducts;
    }

    public static function addNodeProductPositions(Node $node, ArrayObject $productPositions): array
    {
        $value = $node->getValue();
        $categoryId = $value['categoryId'];
        $products = $value['products'] ?? [];

        if (empty($products)) {
            return [];
        }

        foreach ($products as $productId => $position) {
            $productPositions[] = [
                'categoryId' => $categoryId,
                'productId' => $productId,
                'position' => $position,
            ];
        }

        return $products;
    }
}