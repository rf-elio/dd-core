<?php


namespace Elio\FactFinder\Core\ProductBundle;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\RecommendationRequest;
use Elio\FactFinder\Api\Records\Response\ProductsResponse;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\ProductBundle\Exception\ProductBundleException;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class RecommendedBundle implements ProductBundleInterface
{
    public const TYPE = 'recommendation';

    private RecordsApi $recordsApi;
    private FactFinderConfigServiceInterface $configService;

    public function __construct(RecordsApi $recordsApi, FactFinderConfigServiceInterface $configService)
    {
        $this->recordsApi = $recordsApi;
        $this->configService = $configService;
    }

    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }

    public function getProducts(Request $request, SalesChannelContext $salesChannelContext): ProductCollection
    {
        $config = $this->configService->getByContext($salesChannelContext);
        if (!$config->isActive() || !$config->recommendedProductsActive()) {
            throw new ProductBundleException('Recommended products are not active');
        }
        if (empty($request->get('ids'))) {
            throw new ProductBundleException('Param ids does not exists');
        }

        $recommendationRequest = new RecommendationRequest($config->getApiChannel());
        $recommendationRequest->setIds($request->get('ids'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());

        $resultCollection = $this->recordsApi->getRecommendations($recommendationRequest, $salesChannelContext);
        /** @var ProductsResponse $products */
        $products = $resultCollection->get(ProductsResponse::class);

        return $products->getProducts();
    }
}
