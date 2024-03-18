<?php

namespace Elio\ElioDataDiscovery\Core\ProductBundle;

use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\ProductBundle\Exception\NoProductBundleHandlerFoundException;
use Elio\ElioDataDiscovery\Core\ProductBundle\Handler\ProductBundleHandlerInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductBundleService
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 * @author Ralf Frommherz
 */
class ProductBundleService implements ProductBundleServiceInterface
{
    /**
     * @var iterable|ProductBundleHandlerInterface[]
     */
    private iterable $productBundles;
    private ElioDataDiscoveryConfigServiceInterface $configService;

    /**
     * @param iterable|ProductBundleHandlerInterface[] $productBundles
     */
    public function __construct(iterable $productBundles, ElioDataDiscoveryConfigServiceInterface $configService)
    {
        $this->productBundles = $productBundles;
        $this->configService = $configService;
    }

    /**
     * Fetches product bundles for the given bundle type
     *
     * @param string $type
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @return ProductCollection
     */
    public function getProducts(string $type, Request $request, Criteria $criteria, SalesChannelContext $salesChannelContext): ProductCollection
    {
        $config = $this->configService->getByContext($salesChannelContext);

        if(!$config->isActive()) {
            return new ProductCollection();
        }

        foreach ($this->productBundles as $productBundle) {
            if($productBundle->supports($type)) {
                return $productBundle->getProducts($request, $criteria, $salesChannelContext);
            }
        }

        throw new NoProductBundleHandlerFoundException(sprintf(
            'No product bundle handler for type "%s" found', $type
        ));
    }
}