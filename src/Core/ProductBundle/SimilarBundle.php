<?php


namespace Elio\FactFinder\Core\ProductBundle;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\SimilarRequest;
use Elio\FactFinder\Api\Records\Response\ProductsResponse;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\ProductBundle\Exception\ProductBundleException;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class SimilarBundle implements ProductBundleInterface
{
    public const TYPE = 'similar';

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
        if (!$config->isActive() || !$config->similarProductsActive()) {
            throw new ProductBundleException('Similar products are not active');
        }
        if ($request->get('id') === null) {
            throw new ProductBundleException('Param id does not exists');
        }

        $similarRequest = new SimilarRequest($config->getApiChannel());
        $similarRequest->setId($request->get('id'));

        $resultCollection = $this->recordsApi->getSimilar($similarRequest, $salesChannelContext);
        /** @var ProductsResponse $products */
        $products = $resultCollection->get(ProductsResponse::class);

        return $products->getProducts();
    }
}
