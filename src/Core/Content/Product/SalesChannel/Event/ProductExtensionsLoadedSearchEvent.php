<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductExtensionsLoadedSearchEvent extends ProductExtensionsLoadedEvent
{
    public function __construct(
        private ProductCollection $products,
        SalesChannelContext $context,
    )
    {
        parent::__construct($context);
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}