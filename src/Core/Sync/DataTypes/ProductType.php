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

namespace Elio\ElioSearch\Core\Sync\DataTypes;

use Shopware\Core\Content\Product\ProductEntity;

class ProductType extends ProductEntity implements TypeInterface
{
    public static function mapFromProduct(ProductEntity $product): self
    {
        $self = new self();
        $self->setId($product->getId());
        $self->setProductReviews($product->getProductReviews());
        $self->setParentId($product->getParentId());
        $self->setManufacturerId($product->getManufacturerId());
        $self->setUnitId($product->getUnitId());
        $self->setActive($product->getActive());
        $self->setPrice($product->getPrice());
        $self->setManufacturerNumber($product->getManufacturerNumber());
        $self->setEan($product->getEan());
        $self->setSales($product->getSales());
        $self->setStock($product->getStock());
        $self->setIsCloseout($product->getIsCloseout());
        $self->setPurchaseSteps($product->getPurchaseSteps());
        $self->setMaxPurchase($product->getMaxPurchase());
        $self->setMinPurchase($product->getMinPurchase());
        $self->setPurchaseUnit($product->getPurchaseUnit());
        $self->setReferenceUnit($product->getReferenceUnit());
        $self->setShippingFree($product->getShippingFree());
        $self->setPurchasePrices($product->getPurchasePrices());
        $self->setMarkAsTopseller($product->getMarkAsTopseller());
        $self->setWeight($product->getWeight());
        $self->setWidth($product->getWidth());
        $self->setHeight($product->getHeight());
        $self->setLength($product->getLength());
        $self->setReleaseDate($product->getReleaseDate());
        $self->setCategoryTree($product->getCategoryTree());
        $self->setName($product->getName());
        $self->setKeywords($product->getKeywords());
        $self->setDescription($product->getDescription());
        $self->setMetaTitle($product->getMetaTitle());
        $self->setPackUnit($product->getPackUnit());
        $self->setPackUnitPlural($product->getPackUnitPlural());
        $self->setTax($product->getTax());
        $self->setManufacturer($product->getManufacturer());
        $self->setUnit($product->getUnit());
        $self->setPrices($product->getPrices());
        $self->setRestockTime($product->getRestockTime());
        $self->setStreamIds($product->getStreamIds());
        $self->setOptionIds($product->getOptionIds());
        $self->setPropertyIds($product->getPropertyIds());
        $self->setCover($product->getCover());
        $self->setCmsPage($product->getCmsPage());
        $self->setCmsPageId($product->getCmsPageId());
        $self->setSlotConfig($product->getSlotConfig());
        $self->setParent($product->getParent());
        $self->setChildren($product->getChildren());
        $self->setMedia($product->getMedia());
        $self->setSearchKeywords($product->getSearchKeywords());
        $self->setTranslations($product->getTranslations());
        $self->setCategories($product->getCategories());
        $self->setCustomFieldSets($product->getCustomFieldSets());
        $self->setTags($product->getTags());
        $self->setProperties($product->getProperties());
        $self->setOptions($product->getOptions());
        $self->setConfiguratorSettings($product->getConfiguratorSettings());
        $self->setCategoriesRo($product->getCategoriesRo());
        $self->setAutoIncrement($product->getAutoIncrement());
        $self->setCoverId($product->getCoverId());
        $self->setBlacklistIds($product->getBlacklistIds());
        $self->setWhitelistIds($product->getWhitelistIds());
        $self->setVisibilities($product->getVisibilities());
        $self->setProductNumber($product->getProductNumber());
        $self->setTagIds($product->getTagIds());
        $self->setVariantRestrictions($product->getVariantRestrictions());
        $self->setVariantListingConfig($product->getVariantListingConfig());
        $self->setVariation($product->getVariation());
        $self->setAvailableStock($product->getAvailableStock());
        $self->setAvailable($product->getAvailable());
        $self->setDeliveryTimeId($product->getDeliveryTimeId());
        $self->setDeliveryTime($product->getDeliveryTime());
        $self->setChildCount($product->getChildCount());
        $self->setRatingAverage($product->getRatingAverage());
        $self->setDisplayGroup($product->getDisplayGroup());
        $self->setMainCategories($product->getMainCategories());
        $self->setMetaDescription($product->getMetaDescription());
        $self->setSeoUrls($product->getSeoUrls());
        $self->setOrderLineItems($product->getOrderLineItems());
        $self->setCrossSellings($product->getCrossSellings());
        $self->setCrossSellingAssignedProducts($product->getCrossSellingAssignedProducts());
        $self->setFeatureSetId($product->getFeatureSetId());
        $self->setFeatureSet($product->getFeatureSet());
        $self->setCustomFieldSetSelectionActive($product->getCustomFieldSetSelectionActive());
        $self->setCustomSearchKeywords($product->getCustomSearchKeywords());
        $self->setWishlists($product->getWishlists());
        $self->setCanonicalProductId($product->getCanonicalProductId());
        $self->setCanonicalProduct($product->getCanonicalProduct());
        $self->setStreams($product->getStreams());
        $self->setCategoryIds($product->getCategoryIds());
        $self->setDownloads($product->getDownloads());
        $self->setStates($product->getStates());
        $self->setUniqueIdentifier($product->getUniqueIdentifier());
        $self->setVersionId($product->getVersionId());
        $self->setTranslated($product->getTranslated());
        $self->setCreatedAt($product->getCreatedAt());
        $self->setUpdatedAt($product->getUpdatedAt());
        $self->setExtensions($product->getExtensions());

        return $self;
    }
}