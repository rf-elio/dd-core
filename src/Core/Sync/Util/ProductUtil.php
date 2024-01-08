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

namespace Elio\ElioSearch\Core\Sync\Util;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Elio\ElioSearch\Core\Sync\Util\ValueUtil;

/**
 * Class ProductUtil
 * @package Elio\ElioSearch\Core\Sync\Util
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductUtil
{
    /**
     * Checks if product should be displayed by default
     *
     * @param ProductEntity $product
     * @param ProductEntity|null $parentProduct
     * @return bool
     */
    public static function isDisplayedByDefault(ProductEntity $product, ?ProductEntity $parentProduct): bool
    {
        if (!$parentProduct) {
            $displayParent = $product->getVariantListingConfig()?->getDisplayParent();
            return $product->getChildCount() < 1 || $displayParent === true;
        }

        if (!$variantListingConfig = $parentProduct->getVariantListingConfig()) {
            return false;
        }

        // only the parent product is allowed to be shown
        if ($variantListingConfig->getDisplayParent()) {
            return !$product->getParentId();
        }

        // specific variant selected
        if ($product->getId() === $variantListingConfig->getMainVariantId()) {
            return true;
        }


        // specific property should be shown
        if ($variantListingConfig->getDisplayParent() === null && $variantListingConfig->getMainVariantId() === null) {
            if (empty($variantListingConfig->getConfiguratorGroupConfig())) {
                return false;
            }

            $children = $parentProduct->getChildren() ?: [];
            $displayGroups = [];
            foreach ($children as $child) {
                if (!in_array($child->getDisplayGroup(), $displayGroups, true)) {
                    $displayGroups[$child->getId()] = $child->getDisplayGroup();
                }
            }

            return array_key_exists($product->getId(), $displayGroups);
        }

        return false;
    }

    public static function getGroupingKey(ProductEntity $product, ?ProductEntity $parentProduct): string
    {
        $masterProductNumber = $parentProduct?->getProductNumber() ?? $product->getProductNumber();
        $configuratorGroupConfig = $product->getVariantListingConfig()?->getConfiguratorGroupConfig();

        $groupingKey = $masterProductNumber;

        if (!$configuratorGroupConfig) {
            return $groupingKey;
        }

        /** @var array{id:string,representation:string,expressionForListings:bool} $configuratorGroupConfig */

        $ids = array_map(function ($groupConfig) {
            if (!($groupConfig['expressionForListings'] ?? false)) {
                return null;
            }

            return $groupConfig['id'] ?: null;
        }, $configuratorGroupConfig ?: []);

        $ids = array_filter($ids, fn ($id) => $id !== null);

        /** @var array<string,object{name:string,value:string}> */
        $options = $product
            ->getOptions()
            ->filter(function (PropertyGroupOptionEntity $propertyGroupOption) use ($ids) {
                return in_array($propertyGroupOption->getGroupId(), $ids);
            })
            ->map(function (PropertyGroupOptionEntity $propertyGroupOption) {
                $propertyGroup = $propertyGroupOption->getGroup();

                $name  = $propertyGroup->getTranslation('name') ?? $propertyGroup->getName() ?? '';
                $value = ValueUtil::cleanValue($propertyGroupOption->getTranslation('name') ?? $propertyGroup->getName()) ?: '';

                return (object)compact('name', 'value');
            });

        $slugger = new AsciiSlugger();

        $optionPairs = array_map(fn ($option) => strtolower($slugger->slug($option->name) . ':' . $slugger->slug($option->value)), $options);

        asort($optionPairs); // make sure it is consistant

        $groupingKey = rtrim(implode('@', [$groupingKey, implode(';', $optionPairs)]), '@');

        return $groupingKey;
    }
}
