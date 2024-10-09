<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Util;

use Elio\ElioDataDiscovery\Configuration\Configuration;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;

class Excluder
{
    /**
     * @param ProductCollection $collection
     * @param Configuration $config
     *
     * @return ProductCollection
     */
    public static function excludeProductsFromRecommendations(ProductCollection $collection, Configuration $config): ProductCollection
    {
        if (empty($config->getRecommendationExcludedProducts())) {
            return $collection;
        }

        return $collection->filter(
            static fn (ProductEntity $product) => !in_array($product->getId(), $config->getRecommendationExcludedProducts(), true)
        );
    }
}
