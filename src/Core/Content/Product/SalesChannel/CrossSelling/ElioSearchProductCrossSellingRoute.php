<?php

namespace Elio\ElioSearch\Core\Content\Product\SalesChannel\CrossSelling;

use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\ProductBundle\Handler\RecommendedBundleHandlerHandler;
use Elio\ElioSearch\Core\ProductBundle\Handler\SimilarBundleHandlerHandler;
use Elio\ElioSearch\Core\ProductBundle\ProductBundleServiceInterface;
use Shopware\Core\Content\Product\Aggregate\ProductCrossSelling\ProductCrossSellingEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElement;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\CrossSellingElementCollection;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\ProductCrossSellingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ElioSearchProductCrossSellingRoute
 * @package Elio\ElioSearch\Core\Content\Product\SalesChannel\Detail
 * @author Ralf Frommherz
 */
class ElioSearchProductCrossSellingRoute extends AbstractProductCrossSellingRoute
{
    private AbstractProductCrossSellingRoute $crossSellingRoute;
    private ElioSearchConfigServiceInterface $configService;
    private ProductBundleServiceInterface $productBundleService;
    private TranslatorInterface $translator;

    /**
     * @param AbstractProductCrossSellingRoute $crossSellingRoute
     * @param ElioSearchConfigServiceInterface $configService
     * @param ProductBundleServiceInterface $productBundleService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        AbstractProductCrossSellingRoute $crossSellingRoute,
        ElioSearchConfigServiceInterface $configService,
        ProductBundleServiceInterface $productBundleService,
        TranslatorInterface $translator
    )
    {
        $this->crossSellingRoute = $crossSellingRoute;
        $this->configService = $configService;
        $this->productBundleService = $productBundleService;
        $this->translator = $translator;
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
        $productCrossSellingResponse = $this->getDecorated()->load($productId, $request, $context, $criteria);

        if (!$config->isActive() || !$request->isXmlHttpRequest()) {
            return $productCrossSellingResponse;
        }

        /** @var CrossSellingElementCollection $crossSellingElementCollection */
        $crossSellingElementCollection = $productCrossSellingResponse->getObject();

        $crossSellingCriteria = new Criteria();
        $crossSellingCriteria->setLimit($config->getProductDetailSliderLimit());
        if ($config->isUseProductDetailRecommendations()) {
            $request->request->set('productIds', [$productId]);
            $products = $this->productBundleService->getProducts(
                RecommendedBundleHandlerHandler::TYPE, $request, $crossSellingCriteria, $context
            );
            $crossSellingElementCollection->add($this->createCrossSellingElement(
                'elioSearch.cross-selling.recommendations',
                $products
            ));
        }

        if ($config->isUseProductDetailSimilar()) {
            $request->request->set('productId', $productId);
            $products = $this->productBundleService->getProducts(
                SimilarBundleHandlerHandler::TYPE, $request, $crossSellingCriteria, $context
            );
            $crossSellingElementCollection->add($this->createCrossSellingElement(
                'elioSearch.cross-selling.similar',
                $products
            ));
        }

        return $productCrossSellingResponse;
    }

    /**
     * Creates the cross selling element based on the elio search product collection
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
}