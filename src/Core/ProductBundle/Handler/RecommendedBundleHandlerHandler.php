<?php

namespace Elio\ElioDataDiscovery\Core\ProductBundle\Handler;

use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationApi;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\RecommendationRequest;
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
 * Class RecommendedBundle
 *
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 */
class RecommendedBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'recommendation';

    private RecommendationApi $RecommendationApi;
    private ElioDataDiscoveryConfigServiceInterface $configService;

    /**
     * RecommendedBundle constructor.
     *
     * @param RecommendationApi $RecommendationApi
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     */
    public function __construct(RecommendationApi $RecommendationApi, ElioDataDiscoveryConfigServiceInterface $configService)
    {
        $this->RecommendationApi = $RecommendationApi;
        $this->configService = $configService;
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

        if (!$config->isUseProductDetailRecommendations()) {
            return new ProductCollection();
        }

        if (empty($request->get('productIds'))) {
            throw new ProductBundleInvalidRequestException('Param "productIds" does not exists');
        }

        $recommendationRequest = new RecommendationRequest('');
        $recommendationRequest->setIds($request->get('productIds'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());
        $recommendationRequest->setMaxResults($criteria->getLimit() ?? 0);

        $resultCollection = $this->RecommendationApi->getRecommendations($recommendationRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
