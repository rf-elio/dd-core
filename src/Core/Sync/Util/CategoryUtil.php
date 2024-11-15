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

namespace Elio\ElioDataDiscovery\Core\Sync\Util;

use ArrayObject;
use Elio\ElioDataDiscovery\Core\Util\Tree\Node;
use Elio\ElioDataDiscovery\Core\Util\Tree\RandomAddTree;
use Elio\ElioDataDiscovery\ElioDataDiscovery;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CategoryUtil
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class CategoryUtil
{
    /**
     * Loops over each category leven and inherits the custom fields to child categories.
     *
     * Special handling:
     *  - inherited type: The type configured to be inherited to child categories is applied if the child category has
     *                    not an own type configured.
     *
     * @param Node[] $nodes
     * @param ArrayObject<mixed> $customFields
     * @param array $inheritedCustomFields
     */
    private static function buildCustomFieldInheritanceByNodes(array $nodes, ArrayObject $customFields, array $inheritedCustomFields = []): void
    {
        foreach ($nodes as $node) {
            /** @var CategoryEntity $category */
            $category = $node->getValue();
            $categoryCustomFields = $category->getTranslation('customFields') ?? $category->getCustomFields() ?? [];
            $categoryCustomFields = array_filter($categoryCustomFields);

            // add parent custom fields
            $mergedCategoryCustomFields = array_replace($inheritedCustomFields, $categoryCustomFields);

            // apply the category type for child categories to child categories that don't have an own type
            // CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT: update
            if(
                (
                    !isset($categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT]) ||
                    empty($categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT])
                ) &&
                isset($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED]) &&
                !empty($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED])
            ) {
                $mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT] = $mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED];
            }

            // CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT: cleanup
            if (
                isset($categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT]) &&
                !empty($categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT]) &&
                (
                    !isset($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED]) ||
                    empty($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED])
                )
            ) {
                $mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT] = '';
            }

            // CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE: will not be inherited (should only exclude this category, but not the children)
            if(
                !(
                    isset($categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]) &&
                    $categoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]
                )
            ) {
                unset($mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]); // not merging the exclude flag
            }

            // CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE -> if the parent is excluding children, or excluded itself by parent
            if (
                (
                    isset($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]) &&
                    $inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]
                ) || (
                    isset($inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE]) &&
                    $inheritedCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE]
                )
            ) {
                $mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE] = true;
            } else {
                $mergedCategoryCustomFields[ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE] = false;
            }

            $customFields[$category->getId()] = $mergedCategoryCustomFields;
            self::buildCustomFieldInheritanceByNodes($node->getChildNodes(), $customFields, $mergedCategoryCustomFields);
        }
    }

    /**
     * Creates a tree for the given categories
     * @param EntityCollection $categories
     * @return RandomAddTree
     */
    private static function createCategoryTree(EntityCollection $categories): RandomAddTree
    {
        $tree = new RandomAddTree();
        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            $tree->add($category->getId(), $category->getParentId(), $category);
        }
        return $tree;
    }

    /**
     * Creates a tree by all categories. The tree will be looped over to generate the custom field inheritance
     *
     * @param SalesChannelRepository $categoryRepository
     * @param SalesChannelContext $context
     * @return array
     */
    public static function buildCustomFieldInheritance(SalesChannelRepository $categoryRepository, SalesChannelContext $context): array
    {
        $categories = $categoryRepository->search(new Criteria(), $context);
        return self::buildCustomFieldInheritanceForCategories($categories);
    }

    public static function buildCustomFieldInheritanceForCategories(EntityCollection $categories): array
    {
        $tree = self::createCategoryTree($categories);
        $customFields = new ArrayObject();
        self::buildCustomFieldInheritanceByNodes($tree->create(), $customFields);
        return [
            'tree' => $tree,
            'customFields' => $customFields->getArrayCopy()
        ];
    }
}
