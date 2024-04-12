<?php

namespace Elio\ElioDataDiscovery\Core\ProductBundle\Handler;

use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationApi;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\SimilarRequest;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\ProductBundle\Exception\ProductBundleInvalidRequestException;
use Elio\ElioDataDiscovery\Core\ProductBundle\Excluder;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class SimilarBundleHandler
 *
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 */
class SimilarBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'similar';

    /**
     * SimilarBundle constructor.
     *
     * @param RecommendationApi $recommendationApi
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     */
    public function __construct(
        private readonly RecommendationApi                       $recommendationApi,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService
    )
    {
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function getProducts(Request $request, Criteria $criteria, SalesChannelContext $salesChannelContext): ProductCollection
    {
        $config = $this->configService->getByContext($salesChannelContext);

        if (!$config->isUseProductDetailSimilar()) {
            return new ProductCollection();
        }

        if ($request->get('productId') === null) {
            throw new ProductBundleInvalidRequestException('Param "id" does not exists');
        }

        $similarRequest = new SimilarRequest('');
        $similarRequest->setProductNumber($request->get('productNumber'));
        $similarRequest->setLimit($criteria->getLimit() ?? 0);

        $resultCollection = $this->recommendationApi->getSimilar($similarRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        if (!$productListing) {
            return new ProductCollection();
        }
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
