<?php

namespace Elio\ElioDataDiscovery\Core\ProductBundle;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductBundleService
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 * @author Ralf Frommherz
 */
interface ProductBundleServiceInterface
{
    /**
     * Fetches product bundles for the given bundle type
     *
     * @param string $type
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @return ProductCollection
     */
    public function getProducts(string $type, Request $request, Criteria $criteria, SalesChannelContext $salesChannelContext): ProductCollection;
}