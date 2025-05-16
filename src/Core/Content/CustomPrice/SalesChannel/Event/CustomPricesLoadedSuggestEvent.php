<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\CustomPrice\SalesChannel\Event;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomPricesLoadedSuggestEvent extends CustomPricesLoadedEvent
{
    public function __construct(
        string $customerId,
        SalesChannelContext $context,
        private array $products
    )
    {
        parent::__construct($customerId, $context);
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
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }
}
