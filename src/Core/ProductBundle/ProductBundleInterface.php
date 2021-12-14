<?php


namespace Elio\FactFinder\Core\ProductBundle;


use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface ProductBundleInterface
{
    public function supports(string $type): bool;

    public function getProducts(Request $request, SalesChannelContext $salesChannelContext): ProductCollection;
}
