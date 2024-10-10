<?php

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\CrossSelling;

use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationAdapter;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\RecommendationRequest;
use Elio\ElioDataDiscovery\Api\Recommendations\Response\RecommendationResponse;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Elio\ElioDataDiscovery\Core\Util\Excluder;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElement;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\ProductCrossSellingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * Class ElioDataDiscoveryProductCrossSellingRoute
 * @package Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Detail
 * @author Ralf Frommherz
 */
class ElioDataDiscoveryProductCrossSellingRoute extends AbstractProductCrossSellingRoute
{
    use ElioDataDiscoveryLogTrait;

    /**
     * @param AbstractProductCrossSellingRoute $crossSellingRoute
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     * @param TranslatorInterface $translator
     * @param EntityRepository $productRepository
     * @param RecommendationAdapter $recommendationApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AbstractProductCrossSellingRoute $crossSellingRoute,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService,
        private readonly TranslatorInterface $translator,
        private readonly EntityRepository $productRepository,
        private readonly RecommendationAdapter $recommendationApi,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @return AbstractProductCrossSellingRoute
     */
    public function getDecorated(): AbstractProductCrossSellingRoute
    {
        return $this->crossSellingRoute;
    }

    /**
     * Adds empty collection entries to create the required tabs in the shopware template. The product slider is
     * updated via ajax. That's why we only load the sliders for xhr requests.
     *
     * @param string $productId
     * @param Request $request
     * @param SalesChannelContext $context
     * @param Criteria $criteria
     * @return ProductCrossSellingRouteResponse
     * @throws Throwable
     */
    public function load(
        string $productId,
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): ProductCrossSellingRouteResponse {
        $config = $this->configService->getByContext($context);
        $productCrossSellingResponse = $this->getDecorated()->load($productId, $request, $context, $criteria);

        if (!$config->isActive() || !$request->isXmlHttpRequest()) {
            return $productCrossSellingResponse;
        }

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search(new Criteria([$productId]), $context->getContext())->first();
        $productNumber = $product?->getProductNumber();

        /** @var CrossSellingElementCollection $crossSellingElementCollection */
        $crossSellingElementCollection = $productCrossSellingResponse->getObject();

        try {
            $crossSellingCriteria = new Criteria();
            $crossSellingCriteria->setLimit($config->getProductDetailSliderLimit());
            $disabledRecommendationTypes = explode(',', $config->getDisabledRecommendationTypes());
            $request->request->set('productIds', [$productId]);
            $request->request->set('productNumber', $productNumber);
            $recommendations = $this->getProducts($request, $crossSellingCriteria, $config, $context);
            foreach ($recommendations as $recommendationType => $recommendation) {
                if (!in_array($recommendationType, $disabledRecommendationTypes, true)) {
                    $crossSellingElementCollection->add($this->createCrossSellingElement(
                        'elioDataDiscovery.cross-selling.' . $recommendationType, $recommendation
                    ));
                }
            }
        } catch (Throwable $e) {
            $this->searchError('Could not load cross selling elements', $this, [
                'exception' => $e,
                'productId' => $productId,
                'request' => $request,
                'context' => $context,
                'criteria' => $criteria
            ]);
        }

        return $productCrossSellingResponse;
    }

    /**
     * Creates the cross selling element based on the ff product collection
     *
     * @param string $name
     * @param ProductCollection $products
     * @return CrossSellingElement
     */
    protected function createCrossSellingElement(string $name, ProductCollection $products): CrossSellingElement
    {
        $crossSelling = new ProductCrossSellingEntity();
        $crossSelling->setId(Uuid::randomHex());
        $crossSelling->setActive(true);
        $crossSelling->setName($this->translator->trans($name, []));
        $crossSelling->setTranslated(['name' => $crossSelling->getName()]);
        $crossSelling->setPosition(0);
        $crossSelling->setType('productlisting');

        $crossSellingElement = new CrossSellingElement();
        $crossSellingElement->setTotal($products->count());
        $crossSellingElement->setProducts($products);
        $crossSellingElement->setCrossSelling($crossSelling);
        return $crossSellingElement;
    }

    /**
     * @throws Throwable
     */
    protected function getProducts(
        Request $request,
        Criteria $criteria,
        Configuration $config,
        SalesChannelContext $salesChannelContext
    ): array {
        $products = [];
        $recommendationRequest = new RecommendationRequest('');
        $recommendationRequest->setProductNumber($request->get('productNumber'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());
        $recommendationRequest->setLimit($criteria->getLimit() ?? 0);

        $resultCollection = $this->recommendationApi->getRecommendations($recommendationRequest, $salesChannelContext);
        /** @var RecommendationResponse $result */
        foreach ($resultCollection as $result) {
            $productListing = $result->getProductListing();
            if (!$productListing) {
                $products[$result->getRecommendationType()] = new ProductCollection();
            }
            $productCollection = Excluder::excludeProductsFromRecommendations($productListing->getProducts(), $config);
            if ($productCollection->count() > 0) {
                $products[$result->getRecommendationType()] = $productCollection;
            }
        }
        return $products;
    }
}
