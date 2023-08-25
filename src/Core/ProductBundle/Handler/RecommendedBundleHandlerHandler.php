<?php


namespace Elio\ElioSearch\Core\ProductBundle\Handler;


use Elio\ElioSearch\Api\Records\RecordsApi;
use Elio\ElioSearch\Api\Records\Request\RecommendationRequest;
use Elio\ElioSearch\Api\Search\Response\ProductListingResponse;
use Elio\ElioSearch\Configuration\FactFinderConfigServiceInterface;
use Elio\ElioSearch\Core\ProductBundle\Exception\ProductBundleInvalidRequestException;
use Elio\ElioSearch\Core\ProductBundle\Excluder;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class RecommendedBundle
 *
 * @package Elio\ElioSearch\Core\ProductBundle
 */
class RecommendedBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'recommendation';

    private RecordsApi $recordsApi;
    private FactFinderConfigServiceInterface $configService;

    /**
     * RecommendedBundle constructor.
     *
     * @param RecordsApi $recordsApi
     * @param FactFinderConfigServiceInterface $configService
     */
    public function __construct(RecordsApi $recordsApi, FactFinderConfigServiceInterface $configService)
    {
        $this->recordsApi = $recordsApi;
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

        $recommendationRequest = new RecommendationRequest($config->getApiChannel());
        $recommendationRequest->setIds($request->get('productIds'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());
        $recommendationRequest->setMaxResults($criteria->getLimit());

        $resultCollection = $this->recordsApi->getRecommendations($recommendationRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
