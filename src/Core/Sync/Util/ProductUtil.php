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

namespace Elio\ElioDataDiscovery\Core\Sync\Util;

use Elio\ElioDataDiscovery\Core\Defaults;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\System\Currency\CurrencyCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;

/**
 * Class ProductUtil
 * @package Elio\ElioDataDiscovery\Core\Sync\Util
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @author Andrei Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductUtil
{

    /**
     * Searches for the best matching thumbnail
     *
     * @param MediaThumbnailCollection|null $thumbnailCollection
     * @param int $targetSize
     * @return string
     */
    public static function getThumbnailUrl(?MediaThumbnailCollection $thumbnailCollection, int $targetSize): string
    {
        if (!$thumbnailCollection || $thumbnailCollection->count() <= 0) {
            return '';
        }

        $bestMatching = null;
        $bestMatchingSizeDifference = 0;
        foreach ($thumbnailCollection as $thumbnail) {
            $targetSizeDifference = abs($targetSize - $thumbnail->getWidth());
            if (!$bestMatching || $targetSizeDifference < $bestMatchingSizeDifference) {
                $bestMatching = $thumbnail;
                $bestMatchingSizeDifference = $targetSizeDifference;
            }
        }

        return $bestMatching ? $bestMatching->getUrl() : '';
    }

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

    /**
     * Generates a grouping key for a product
     *
     * @param ProductEntity $product The product for which the grouping key is generated
     * @param ProductEntity|null $parentProduct The parent product, if available
     * @return string The grouping key
     */
    public static function getGroupingKey(ProductEntity $product, ?ProductEntity $parentProduct): string
    {
        $masterProductNumber = $parentProduct?->getProductNumber() ?? $product->getProductNumber();
        $configuratorGroupConfig = $product->getVariantListingConfig()?->getConfiguratorGroupConfig();
        $groupingKey = $masterProductNumber;

        if (!$configuratorGroupConfig) {
            return $groupingKey;
        }

        /** @var array{id:string,representation:string,expressionForListings:bool|null}[] $configuratorGroupConfig */
        $ids = array_map(static function ($groupConfig) {
            if (!($groupConfig['expressionForListings'] ?? false)) {
                return null;
            }

            return $groupConfig['id'] ?: null;
        }, $configuratorGroupConfig);

        $ids = array_filter($ids, static fn ($id) => $id !== null);

        /** @var array<string,object{name:string,value:string}> */
        $options = $product
            ->getOptions()
            ?->filter(fn(PropertyGroupOptionEntity $propertyGroupOption) => in_array($propertyGroupOption->getGroupId(), $ids))
            ->map(function (PropertyGroupOptionEntity $propertyGroupOption) {
                $propertyGroup = $propertyGroupOption->getGroup();
                if ($propertyGroup) {
                    $name  = $propertyGroup->getTranslation('name') ?? $propertyGroup->getName() ?? '';
                    $value = ValueUtil::cleanValue($propertyGroupOption->getTranslation('name') ?? $propertyGroup->getName()) ?: '';
                } else {
                    $name  = '';
                    $value = '';
                }
                return (object)compact('name', 'value');
            });

        $slugger = new AsciiSlugger();

        $optionPairs = array_map(fn ($option) => strtolower($slugger->slug($option->name) . ':' . $slugger->slug($option->value)), $options);

        asort($optionPairs); // make sure it is consistant

        $groupingKey = rtrim(implode('@', [$groupingKey, implode(';', $optionPairs)]), '@');

        return $groupingKey;
    }

    /**
     * Fetches the main product price
     *
     * @param ProductEntity $product
     * @return array|null
     */
    public static function getProductPrice(ProductEntity $product) : ?array
    {
        if ($product->getPrice() === null || !$price = $product->getPrice()->first()) {
            return null;
        }

        $redPrice = null;
        if (
            $price->getListPrice() &&
            $price->getListPrice()->getGross() &&
            $price->getListPrice()->getGross() > $price->getGross()
        ) {
            $redPrice = $price->getListPrice()->getGross();
        }

        return [$price->getGross(), $redPrice];
    }

    /**
     * Fetches the product price string with all currencies
     *
     * @param ProductEntity $product
     * @param SalesChannelContext $context
     *
     * @return string
     */
    public static function getProductPrices(ProductEntity $product, SalesChannelContext $context) : string
    {
        [$price] = self::getProductPrice($product) ?? [null];
        if (!$price) {
            return '';
        }

        $prices = [];
        foreach ($context->getSalesChannel()->getCurrencies() ?? new CurrencyCollection() as $currency) {
            $currencyPrice = $price;
            if ($currency->getId() !== $context->getCurrency()->getId()) {
                $currencyPrice *= $currency->getFactor();
            }

            $prices[] = sprintf(
                '%s~~%s=%s',
                $currency->getIsoCode(),
                $currency->getSymbol(),
                ValueUtil::formatPrice($currencyPrice)
            );
        }

        return !empty($prices) ? sprintf(
            '%s%s%s',
            Defaults::VALUE_SEPARATOR,
            implode(Defaults::VALUE_SEPARATOR, $prices),
            Defaults::VALUE_SEPARATOR
        ) : '';
    }

    /**
     * Builds the category path for a product
     *
     * @param ProductEntity $product
     * @return string
     */
    public static function getCategoryIds(ProductEntity $product): string
    {
        if(!$product->getCategories()) {
            return '';
        }

        $productCategoryIds = [];
        $categories = $product->getCategories()->getElements();

        foreach ($categories as $category) {
            $path = $category->getPath();
            $ids = explode('|', (string) $path);
            $ids = array_filter($ids);
            $productCategoryIds[] = implode('/', $ids);
        }

        return implode(Defaults::VALUE_SEPARATOR, $productCategoryIds);
    }

    /**
     * Creates the product tags string
     *
     * @param ProductEntity $product
     * @return array
     */
    public static function getProductTags(ProductEntity $product): array
    {
        if (!$product->getTags()) {
            return [];
        }

        $tags = [];
        foreach ($product->getTags() as $tag) {
            $tags[] = $tag->getTranslation('name') ?? $tag->getName();
        }

        return $tags;
    }

    /**
     * @param ProductEntity $product
     * @return array<PropertyGroupOptionEntity>
     */
    public static function getFilterableProductProperties(ProductEntity $product): array
    {
        if ($product->getProperties() === null) {
            return [];
        }
        return $product->getProperties()->filter(
            static fn (PropertyGroupOptionEntity $option) => $option->getGroup() !== null && $option->getGroup()->getFilterable()
        )->getElements();
    }

    /**
     * @param ProductEntity $product
     * @return array<PropertyGroupOptionEntity>
     */
    public static function getNonFilterableProductProperties(ProductEntity $product): array
    {
        if ($product->getProperties() === null) {
            return [];
        }
        return $product->getProperties()->filter(
            static fn (PropertyGroupOptionEntity $option) => $option->getGroup() !== null && !$option->getGroup()->getFilterable()
        )->getElements();
    }

    /**
     * Appends the product attributes
     *
     * @param array<PropertyGroupOptionEntity> $properties
     * @return array
     */
    public static function getProductAttribute(array $properties): array
    {
        $attributes = [];
        foreach ($properties as $property) {
            $group = $property->getGroup();
            if ($group !== null) {
                $name = $group->getTranslation('name') ?? $group->getName();
                $value = $property->getTranslation('name') ?? $property->getName();
                $attributes[$name] = ValueUtil::cleanValue($value);
            }
        }

        return $attributes;
    }
}
