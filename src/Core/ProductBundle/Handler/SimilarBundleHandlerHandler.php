<?php

namespace Elio\ElioSearch\Core\ProductBundle\Handler;

use Elio\ElioSearch\Api\Recommendations\RecommendationApi;
use Elio\ElioSearch\Api\Recommendations\Request\SimilarRequest;
use Elio\ElioSearch\Api\Search\Response\ProductListingResponse;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\ProductBundle\Exception\ProductBundleInvalidRequestException;
use Elio\ElioSearch\Core\ProductBundle\Excluder;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class SimilarBundleHandler
 *
 * @package Elio\ElioSearch\Core\ProductBundle
 */
class SimilarBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'similar';

    private RecommendationApi $RecommendationApi;
    private ElioSearchConfigServiceInterface $configService;

    /**
     * SimilarBundle constructor.
     *
     * @param RecommendationApi $RecommendationApi
     * @param ElioSearchConfigServiceInterface $configService
     */
    public function __construct(RecommendationApi $RecommendationApi, ElioSearchConfigServiceInterface $configService)
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

        if (!$config->isUseProductDetailSimilar()) {
            return new ProductCollection();
        }

        if ($request->get('productId') === null) {
            throw new ProductBundleInvalidRequestException('Param "id" does not exists');
        }

        $similarRequest = new SimilarRequest('');
        $similarRequest->setId($request->get('productId'));
        $similarRequest->setMaxResults($criteria->getLimit());

        $resultCollection = $this->RecommendationApi->getSimilar($similarRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
