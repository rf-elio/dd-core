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

namespace Elio\ElioSearch\Core\Sync\Util;

use ArrayObject;
use Elio\ElioSearch\Core\Util\Tree\Node;
use Elio\ElioSearch\Core\Util\Tree\RandomAddTree;
use Elio\ElioSearch\ElioSearch;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
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
     */
    public static function buildCustomFieldInheritanceByNodes(array $nodes, ArrayObject $customFields, array $inheritedCustomFields = []): void
    {
        // if we exclude product info in main category we don't inherit its exclusion to child categories
        unset($inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS]);

        foreach ($nodes as $node) {
            /** @var CategoryEntity $category */
            $category = $node->getValue();
            $categoryCustomFields = $category->getTranslation('customFields') ?? $category->getCustomFields() ?? [];
            $categoryCustomFields = array_filter($categoryCustomFields);

            // add parent custom fields
            $mergedCategoryCustomFields = array_replace($inheritedCustomFields, $categoryCustomFields);

            // apply the category type for child categories to child categories that don't have an own type
            if(
                (
                    !isset($categoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE]) ||
                    empty($categoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE])
                ) &&
                isset($inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED]) &&
                !empty($inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED])
            ) {
                $mergedCategoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE] = $mergedCategoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED];
            }

            // CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE: will not be inherited (should only exclude this category, but not the children)
            if(
                !(
                    isset($categoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]) &&
                    $categoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]
                )
            ) {
                unset($mergedCategoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]); // not merging the exclude flag
            }

            // CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE -> if the parent is excluding children, or excluded itself by parent
            if (
                (
                    isset($inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]) &&
                    $inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]
                ) || (
                    isset($inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE]) &&
                    $inheritedCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE]
                )
            ) {
                $mergedCategoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE] = true;
            } else {
                $mergedCategoryCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE] = false;
            }

            $customFields[$category->getId()] = $mergedCategoryCustomFields;
            self::buildCustomFieldInheritanceByNodes($node->getChildNodes(), $customFields, $mergedCategoryCustomFields);
        }
    }

    /**
     * Creates a tree by all categories. The tree will be looped over to generate the custom field inheritance
     *
     * @param EntityRepository $categoryRepository
     * @param Context $context
     * @return array
     */
    public static function buildCustomFieldInheritance(SalesChannelRepository $categoryRepository, SalesChannelContext $context): array
    {
        $categories = $categoryRepository->search(new Criteria(), $context);
        return self::buildCustomFieldInheritanceForCategories($categories);
    }

    public static function buildCustomFieldInheritanceForCategories(EntityCollection $categories): array
    {
        $customFields = new ArrayObject();
        $tree = new RandomAddTree();

        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            $tree->add($category->getId(), $category->getParentId(), $category);
        }

        self::buildCustomFieldInheritanceByNodes($tree->create(), $customFields);
        return ['tree' => $tree, 'customFields' => $customFields->getArrayCopy()];
    }

    /**
     * @param EntityRepository $categoryRepository
     * @param array $categoryIds
     * @param Criteria $baseCriteria
     * @param SalesChannelContext $context
     * @return EntityCollection<CategoryEntity>
     */
    public static function getChildCategories(SalesChannelRepository $categoryRepository, array $categoryIds, Criteria $baseCriteria, SalesChannelContext $context): EntityCollection
    {
        if (empty($categoryIds)) {
            return new EntityCollection([]);
        }

        $criteria = $baseCriteria;
        self::extendCriteriaForChildCategories($criteria, $categoryIds);
        /* @phpstan-ignore-next-line */
        return $categoryRepository->search($criteria, $context->getContext())->getEntities();
    }

    public static function extendCriteriaForChildCategories(Criteria $criteria, array $categoryIds): void
    {
        $categoryFilters = [];
        foreach ($categoryIds as $categoryId) {
            $categoryFilters[] = new EqualsFilter('parentId', $categoryId);
        }
        $criteria->addFilter(new OrFilter($categoryFilters));
    }
}