<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductExtensionsLoadedSuggestEvent extends ProductExtensionsLoadedEvent
{
    public function __construct(
        private array $products,
        SalesChannelContext $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @return SalesChannelProductEntity[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param SalesChannelProductEntity[] $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }
}