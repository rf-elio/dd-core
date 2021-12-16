<?php


namespace Elio\FactFinder\Core\ProductBundle;


use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ProductBundleInterface
 *
 * @package Elio\FactFinder\Core\ProductBundle
 */
interface ProductBundleInterface
{
    /**
     * @param string $type
     *
     * @return bool
     */
    public function supports(string $type): bool;

    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductCollection
     */
    public function getProducts(Request $request, SalesChannelContext $salesChannelContext): ProductCollection;
}
