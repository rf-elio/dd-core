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


use Elio\ElioDataDiscovery\Core\Util\Tree\Node;
use RuntimeException;

/**
 * Class CategoryTreeUtil
 * @package Elio\ElioDataDiscovery\Core\Sorting\Util
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class CategoryTreeUtil
{
    /**
     * @param Node[] $nodes
     * @return array
     */
    public static function sortCategoryTree(array $nodes): array
    {
        if (empty($nodes)) {
            return $nodes;
        }

        $categoryLinks = [];
        foreach ($nodes as $node) {
            $value = $node->getValue();
            $categoryId = $value['categoryId'];
            $afterCategoryId = $value['afterCategoryId'];
            $categoryLinks[$categoryId] = $afterCategoryId;
        }

        // add positions back to categories
        $sortedCategories = self::sortCategories($categoryLinks);
        $maxPosition = empty($sortedCategories) ? 1 : max($sortedCategories) + 1;

        foreach ($nodes as $node) {
            $value = $node->getValue();
            $categoryId = $value['categoryId'];
            $value['position'] = $sortedCategories[$categoryId] ?? $maxPosition++;
            $node->setValue($value);

            // sort children
            $node->setChildNodes(self::sortCategoryTree($node->getChildNodes()));
        }

        usort($nodes, static function (Node $nodeA, Node $nodeB) {
            $positionA = $nodeA->getValue()['position'];
            $positionB = $nodeB->getValue()['position'];
            return $positionA <=> $positionB;
        });

        return $nodes;
    }

    /**
     * Sorts an array of items by their relations.
     *
     * This function takes as input an associative array where keys are the item IDs
     * and values are the IDs of preceding items. It returns a new associative array where
     * keys are item IDs and values are their respective sorted positions.
     *
     * @param array $categories An array of items where keys are item IDs and values are IDs of preceding items
     *
     * @return array An associative array where keys are item IDs and values are their respective positions after sorting.
     *
     * @example
     * $categoryLinks = [
     *     'a' => null,
     *     'b' => 'a',
     *     'c' => 'b',
     * ];
     *
     * $sortedPositions = sortItems($categoryLinks);
     *
     * This will return ['a' => 1, 'b' => 2, 'c' => 3]
     *
     * @throws RuntimeException If a loop is detected in the input array
     *
     * @author AI Assistant
     */
    private static function sortCategories(array $categories): array
    {
        $sortedCategories = [];

        // Step 1: find the starting point (where value is null)
        $currentId = array_search(null, $categories, true);

        // Step 2: loop through the array and add them in order
        while($currentId !== false) {
            $sortedCategories[$currentId] = count($sortedCategories) + 1; //Keep the index 1-based
            $currentId = array_search($currentId, $categories, true);
        }

        return $sortedCategories;
    }
}