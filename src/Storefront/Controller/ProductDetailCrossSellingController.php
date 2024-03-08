<?php declare(strict_types=1);


namespace Elio\ElioSearch\Storefront\Controller;


use Elio\ElioSearch\Core\Logging\ElioSearchLogTrait;
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
 * @package Elio\ElioSearch\Storefront\Controller
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductDetailCrossSellingController extends StorefrontController
{
    use ElioSearchLogTrait;

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
     * @Route("/widgets/elio-search/product-cross-selling/{productId}", name="widgets.e-elio-search.product-cross-selling.detail", methods={"GET"}, defaults={"XmlHttpRequest"=true,"csrf_protected"=false})
     *
     * @param string $productId
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
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
