<?php

namespace Elio\ElioDataDiscovery\Core\ProductBundle\Handler;

use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationApi;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\RecommendationRequest;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\ProductBundle\Excluder;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class CartRecommendedBundleHandler
 *
 * @package Elio\ElioDataDiscovery\Core\ProductBundle
 */
class CartRecommendedBundleHandlerHandler implements ProductBundleHandlerInterface
{
    public const TYPE = 'cartRecommended';

    private RecommendationApi $RecommendationApi;
    private ElioDataDiscoveryConfigServiceInterface $configService;
    private CartService $cartService;
    private EntityRepository $productRepository;

    /**
     * CartRecommendedBundle constructor.
     *
     * @param RecommendationApi $RecommendationApi
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     * @param CartService $cartService
     * @param EntityRepository $productRepository
     */
    public function __construct(
        RecommendationApi $RecommendationApi,
        ElioDataDiscoveryConfigServiceInterface $configService,
        CartService $cartService,
        EntityRepository $productRepository
    ) {
        $this->RecommendationApi = $RecommendationApi;
        $this->configService = $configService;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
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

        $productNumbers = $this->getProductNumbersFromCart($salesChannelContext);
        if (empty($productNumbers)) {
            return new ProductCollection();
        }

        $recommendationRequest = new RecommendationRequest('');
        $recommendationRequest->setIds($productNumbers);
        $recommendationRequest->setSessionId($salesChannelContext->getToken());

        $resultCollection = $this->RecommendationApi->getRecommendations($recommendationRequest, $salesChannelContext);
        $productListing = $resultCollection->get(ProductListingResponse::class);
        return Excluder::exclude($productListing->getProducts(), $config);
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    private function getProductNumbersFromCart(SalesChannelContext $salesChannelContext): array
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $productLineItems = $cart->getLineItems()->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $ids = [];

        foreach ($productLineItems as $lineItem) {
            $ids[] = $lineItem->getReferencedId();
        }
        if (empty($ids)) {
            return [];
        }

        $products = $this->productRepository->search(new Criteria($ids), $salesChannelContext->getContext());
        return $products->map(static fn (ProductEntity $product) => $product->getProductNumber());
    }
}
