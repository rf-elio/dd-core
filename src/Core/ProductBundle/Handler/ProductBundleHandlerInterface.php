<?php

namespace Elio\ElioSearch\Core\ProductBundle\Handler;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ProductBundleInterface
 *
 * @package Elio\ElioSearch\Core\ProductBundle
 */
interface ProductBundleHandlerInterface
{
    /**
     * @param string $type
     *
     * @return bool
     */
    public function supports(string $type): bool;

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductCollection
     */
    public function getProducts(Request $request, Criteria $criteria, SalesChannelContext $salesChannelContext): ProductCollection;
}
