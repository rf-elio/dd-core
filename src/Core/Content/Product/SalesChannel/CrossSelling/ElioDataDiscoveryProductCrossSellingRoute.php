<?php

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\CrossSelling;

use Elio\ElioBatteryIncludedSearchExtension\Configuration\BatteryIncludedConfiguration;
use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationApi;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\RecommendationRequest;
use Elio\ElioDataDiscovery\Api\Recommendations\Response\RecommendationResponse;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\ProductBundle\Handler\RecommendedBundleHandlerHandler;
use Elio\ElioDataDiscovery\Core\ProductBundle\Handler\SimilarBundleHandlerHandler;
use Elio\ElioDataDiscovery\Core\ProductBundle\ProductBundleServiceInterface;
use Elio\ElioDataDiscovery\Core\Util\Excluder;
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
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ElioDataDiscoveryProductCrossSellingRoute
 * @package Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Detail
 * @author Ralf Frommherz
 */
class ElioDataDiscoveryProductCrossSellingRoute extends AbstractProductCrossSellingRoute
{
    /**
     * @param AbstractProductCrossSellingRoute $crossSellingRoute
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     * @param ProductBundleServiceInterface $productBundleService
     * @param TranslatorInterface $translator
     * @param EntityRepository $productRepository
     */
    public function __construct(
        private readonly AbstractProductCrossSellingRoute $crossSellingRoute,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService,
        private readonly ProductBundleServiceInterface $productBundleService,
        private readonly TranslatorInterface $translator,
        private readonly EntityRepository $productRepository,
        private readonly RecommendationApi $recommendationApi,
    )
    {
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
     */
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductCrossSellingRouteResponse
    {
        $config = $this->configService->getByContext($context);

        /** @var BatteryIncludedConfiguration $batteryIncludedConfig */
        $batteryIncludedConfig = $config->getExtension('batteryIncluded');
        $productCrossSellingResponse = $this->getDecorated()->load($productId, $request, $context, $criteria);

        if (!$config->isActive() || !$request->isXmlHttpRequest()) {
            return $productCrossSellingResponse;
        }

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search(new Criteria([$productId]), $context->getContext())->first();
        $productNumber = $product?->getProductNumber();

        /** @var CrossSellingElementCollection $crossSellingElementCollection */
        $crossSellingElementCollection = $productCrossSellingResponse->getObject();

        $crossSellingCriteria = new Criteria();
        $crossSellingCriteria->setLimit($config->getProductDetailSliderLimit());
        $recommendationTypes = ["together", "also", "related"];
        $disabledRecommendationTypes = explode(',', $batteryIncludedConfig->getDisabledRecommendationTypes());
        $request->request->set('productIds', [$productId]);
        $request->request->set('productNumber', $productNumber);
        $recommendations = $this->getProducts($request, $crossSellingCriteria, $config, $context);
        foreach ($recommendationTypes as $recommendationType) {
            if (!in_array($recommendationType, $disabledRecommendationTypes)) {
                $crossSellingElementCollection->add($this->createCrossSellingElement(
                    'elioDataDiscovery.cross-selling.' . $recommendationType, $recommendations[$recommendationType]
                ));
            }
        }

        dd($productCrossSellingResponse);

//        if ($config->isUseProductDetailRecommendations()) {
//            $request->request->set('productIds', [$productId]);
//            $request->request->set('productNumber', $productNumber);
//            $products = $this->productBundleService->getProducts(
//                RecommendedBundleHandlerHandler::TYPE, $request, $crossSellingCriteria, $context
//            );
//            $crossSellingElementCollection->add($this->createCrossSellingElement(
//                'elioDataDiscovery.cross-selling.recommendations',
//                $products
//            ));
//        }
//
//        if ($config->isUseProductDetailSimilar()) {
//            $request->request->set('productId', $productId);
//            $request->request->set('productNumber', $productNumber);
//            $products = $this->productBundleService->getProducts(
//                SimilarBundleHandlerHandler::TYPE, $request, $crossSellingCriteria, $context
//            );
//            $crossSellingElementCollection->add($this->createCrossSellingElement(
//                'elioDataDiscovery.cross-selling.similar',
//                $products
//            ));
//        }

        return $productCrossSellingResponse;
    }

    /**
     * Creates the cross selling element based on the ff product collection
     *
     * @param string $name
     * @param ProductCollection $products
     * @return CrossSellingElement
     */
    protected function createCrossSellingElement(string $name, ProductCollection $products) : CrossSellingElement
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
     * @throws \Exception
     */
    protected function getProducts(
        Request $request,
        Criteria $criteria,
        Configuration $config,
        SalesChannelContext $salesChannelContext
    ): array
    {
        $products = [];
        $recommendationRequest = new RecommendationRequest('');
        $recommendationRequest->setProductIds($request->get('productIds'));
        $recommendationRequest->setSessionId($salesChannelContext->getToken());
        $recommendationRequest->setLimit($criteria->getLimit() ?? 0);

        $resultCollection = $this->recommendationApi->getRecommendations($recommendationRequest, $salesChannelContext);
        /** @var RecommendationResponse $result */
        foreach ($resultCollection as $result) {
            $productListing = $result->getProductListing();
            if (!$productListing) {
                return new ProductCollection();
            }
            $products[$result->getRecommendationType()] = Excluder::exclude($productListing->getProducts(), $config);
        }
        return $products;
    }
}
