<?php


namespace Elio\FactFinder\Core\ProductBundle\Handler;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\SimilarRequest;
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
 * Class SimilarBundleHandler
 *
 * @package Elio\FactFinder\Core\ProductBundle
 */
class SimilarBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'similar';

    private RecordsApi $recordsApi;
    private FactFinderConfigServiceInterface $configService;

    /**
     * SimilarBundle constructor.
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

        if (!$config->isUseProductDetailSimilar()) {
            return new ProductCollection();
        }

        if ($request->get('id') === null) {
            throw new ProductBundleInvalidRequestException('Param "id" does not exists');
        }

        $similarRequest = new SimilarRequest($config->getApiChannel());
        $similarRequest->setId($request->get('id'));

        $resultCollection = $this->recordsApi->getSimilar($similarRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }
}
