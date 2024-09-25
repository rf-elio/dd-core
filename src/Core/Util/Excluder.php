<?php

namespace Elio\ElioDataDiscovery\Core\Util;

use Elio\ElioDataDiscovery\Configuration\Configuration;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Class Excluder
 *
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 */
class Excluder
{
    /**
     * @param ProductCollection $collection
     * @param Configuration $config
     *
     * @return ProductCollection
     */
    public static function exclude(ProductCollection $collection, Configuration $config): ProductCollection
    {
        if (empty($config->getRecommendationExcludedProducts())) {
            return $collection;
        }

        return $collection->filter(
            static fn (ProductEntity $product) => !in_array($product->getId(), $config->getRecommendationExcludedProducts(), true)
        );
    }
}
