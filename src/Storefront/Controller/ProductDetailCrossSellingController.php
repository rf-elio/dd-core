<?php declare(strict_types=1);


namespace Elio\ElioDataDiscovery\Storefront\Controller;


use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\ProductCrossSellingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductDetailCrossSellingController
 * @Route(defaults={"_routeScope"={"storefront"}})
 *
 * @package Elio\ElioDataDiscovery\Storefront\Controller
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductDetailCrossSellingController extends StorefrontController
{
    use ElioDataDiscoveryLogTrait;

    /**
     * ProductBundleController constructor.
     *
     * @param AbstractProductCrossSellingRoute $productCrossSellingRoute
     * @param LoggerInterface $logger
     */
    public function __construct(
        private AbstractProductCrossSellingRoute $productCrossSellingRoute,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @param string $productId
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    #[Route('/widgets/elio-data-discovery/product-cross-selling/{productId}',
        name: 'widgets.e-elio-data-discovery.product-cross-selling.detail',
        defaults: ['csrf_protected' => false, 'XmlHttpRequest' => true],
        methods: ['GET']
    )]
    public function index(string $productId, Request $request, SalesChannelContext $context): Response
    {
        /** @var ProductCrossSellingRouteResponse $productCrossSellingResponse */
        $productCrossSellingResponse = $this->productCrossSellingRoute->load(
            $productId, $request, $context, new Criteria()
        );

        return $this->renderStorefront('@Storefront/storefront/page/product-detail/cross-selling/tabs.html.twig', [
            'crossSellings' => $productCrossSellingResponse->getObject()
        ]);
    }
}
