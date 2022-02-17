<?php


namespace Elio\FactFinder\Core\ProductBundle\Handler;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\RecommendationRequest;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\ProductBundle\Exception\ProductBundleInvalidRequestException;
use Elio\FactFinder\Core\ProductBundle\Excluder;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class RecommendedBundle
 *
 * @package Elio\FactFinder\Core\ProductBundle
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
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function getProducts(Request $request, SalesChannelContext $salesChannelContext): ProductCollection
    {
        $config = $this->configService->getByContext($salesChannelContext);

        if (!$config->isUseProductDetailRecommendations()) {
            return new ProductCollection();
        }

        if (empty($request->get('ids'))) {
            throw new ProductBundleInvalidRequestException('Param "ids" does not exists');
        }

        $recommendationRequest = new RecommendationRequest($config->getApiChannel());
        $recommendationRequest->setIds($request->get('ids'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());

        $resultCollection = $this->recordsApi->getRecommendations($recommendationRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
