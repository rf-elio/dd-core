<?php


namespace Elio\FactFinder\Core\ProductBundle;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\RecommendationRequest;
use Elio\FactFinder\Api\Records\Response\ProductsResponse;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\ProductBundle\Exception\ProductBundleException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class CartRecommendedBundle
 *
 * @package Elio\FactFinder\Core\ProductBundle
 */
class CartRecommendedBundle implements ProductBundleInterface
{
    public const TYPE = 'cartRecommended';

    private RecordsApi $recordsApi;
    private FactFinderConfigServiceInterface $configService;
    private CartService $cartService;

    /**
     * CartRecommendedBundle constructor.
     *
     * @param RecordsApi $recordsApi
     * @param FactFinderConfigServiceInterface $configService
     * @param CartService $cartService
     */
    public function __construct(
        RecordsApi $recordsApi,
        FactFinderConfigServiceInterface $configService,
        CartService $cartService
    ) {
        $this->recordsApi = $recordsApi;
        $this->configService = $configService;
        $this->cartService = $cartService;
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
        if (!$config->isActive()) {
            throw new ProductBundleException('Cart recommended products are not active');
        }
        $ids = $this->getProductIdsFromCart($salesChannelContext);
        if (empty($ids)) {
            return new ProductCollection();
        }

        $recommendationRequest = new RecommendationRequest($config->getApiChannel());
        $recommendationRequest->setIds($ids);
        $recommendationRequest->setSessionId($salesChannelContext->getToken());

        $resultCollection = $this->recordsApi->getRecommendations($recommendationRequest, $salesChannelContext);
        /** @var ProductsResponse $products */
        $products = $resultCollection->get(ProductsResponse::class);
        $collection = $products->getProducts()->filter(
            static fn (ProductEntity $product) => !in_array($product->getId(), $ids, true)
        );

        return Excluder::exclude($collection, $config);
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    private function getProductIdsFromCart(SalesChannelContext $salesChannelContext): array
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $productLineItems = $cart->getLineItems()->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $ids = [];

        foreach ($productLineItems as $lineItem) {
            $ids[] = $lineItem->getReferencedId();
        }
        return $ids;
    }
}
