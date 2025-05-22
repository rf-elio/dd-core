<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\CustomPrice\SalesChannel;

use Shopware\Core\Framework\Struct\Struct;

class CustomPriceItem extends Struct
{
    public function __construct(
        private readonly string $customerId,
        private readonly string $productId,
        private readonly float $price,
        private readonly array $priceExtras,
    ) {}

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function getPriceExtras(): array
    {
        return $this->priceExtras;
    }
}