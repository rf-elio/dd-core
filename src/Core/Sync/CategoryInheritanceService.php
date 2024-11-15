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

namespace Elio\ElioDataDiscovery\Core\Sync;

use Elio\ElioDataDiscovery\Core\Sync\Util\CategoryUtil;
use Elio\ElioDataDiscovery\ElioDataDiscovery;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

/**
 * Class CategoryInheritanceService
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class CategoryInheritanceService
{
    public function __construct(
        private readonly EntityRepository $categoryRepository
    ) {
    }

    public function update(Context $context): void
    {
        $criteria = new Criteria();
        $categories = $this->categoryRepository->search($criteria, $context);
        $result = CategoryUtil::buildCustomFieldInheritanceForCategories($categories);
        $parentalExclude = ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE;
        $exportTypeParent = ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT;
        $exportTypeInherited = ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED;

        if (array_key_exists('customFields', $result) && !empty($result['customFields'])) {
            $dataToUpdate = [];
            foreach ($result['customFields'] as $categoryId => $newCustomFields) {
                /** @var CategoryEntity $category */
                $category = $categories->get($categoryId);
                $oldCustomFields = $category->getCustomFields() ?? [];

                if ($this->hasChanged($newCustomFields, $oldCustomFields, $parentalExclude)) {
                    $oldCustomFields[$parentalExclude] = $newCustomFields[$parentalExclude];
                    if ($this->hasChanged($newCustomFields, $oldCustomFields, $exportTypeInherited)) {
                        $oldCustomFields[$exportTypeParent] = $newCustomFields[$exportTypeInherited];
                    }
                } elseif ($this->hasChanged($newCustomFields, $oldCustomFields, $exportTypeParent)) {
                    $oldCustomFields[$exportTypeParent] = $newCustomFields[$exportTypeParent];
                } else {
                    continue;
                }

                $dataToUpdate[] = [
                    'id' => $categoryId,
                    'customFields' => $oldCustomFields
                ];
            }
            if (!empty($dataToUpdate)) {
                $this->categoryRepository->update($dataToUpdate, $context);
            }
        }
    }

    private function hasChanged(array $newCustomFields, array $oldCustomFields, string $customField): bool
    {
        $parentalExclude = ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE;
        $exportTypeParent = ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_PARENT;

        if ($customField === $parentalExclude) {
            return !(
                array_key_exists($parentalExclude, $oldCustomFields)
                && array_key_exists($parentalExclude, $newCustomFields)
                && $oldCustomFields[$parentalExclude] === $newCustomFields[$parentalExclude]
            );
        }

        if ($customField === $exportTypeParent) {
            return (
                (
                    array_key_exists($exportTypeParent, $newCustomFields)
                    && array_key_exists($exportTypeParent, $oldCustomFields)
                    && $oldCustomFields[$exportTypeParent] !== $newCustomFields[$exportTypeParent]
                )
                ||
                (
                    array_key_exists($exportTypeParent, $newCustomFields)
                    && !array_key_exists($exportTypeParent, $oldCustomFields)
                )
            );
        }

        return false;
    }
}
