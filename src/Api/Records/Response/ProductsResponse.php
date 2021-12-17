<?php


namespace Elio\FactFinder\Api\Records\Response;


use Elio\FactFinder\Api\Response\Response;
use Shopware\Core\Content\Product\ProductCollection;

class ProductsResponse extends Response
{
    protected ProductCollection $products;

    /**
     * @return ProductCollection
     */
    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    /**
     * @param ProductCollection $products
     */
    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}
