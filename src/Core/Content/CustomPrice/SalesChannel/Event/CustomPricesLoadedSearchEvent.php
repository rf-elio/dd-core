<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\CustomPrice\SalesChannel\Event;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomPricesLoadedSearchEvent extends CustomPricesLoadedEvent
{
    public function __construct(
        string $customerId,
        SalesChannelContext $context,
        private ProductCollection $products
    ) {
        parent::__construct($customerId, $context);
    }

    /**
     * @return ProductCollection
     */
    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    /**
     * @param ProductCollection $products
     * @return void
     */
    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}
