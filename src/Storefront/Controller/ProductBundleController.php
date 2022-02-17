<?php


namespace Elio\FactFinder\Storefront\Controller;


use Elio\FactFinder\Core\Logging\FactFinderLogTrait;
use Elio\FactFinder\Core\ProductBundle\ProductBundleService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @RouteScope(scopes={"storefront"})
 * Class ProductBundleController
 *
 * @package Elio\FactFinder\Storefront\Controller
 */
class ProductBundleController extends StorefrontController
{
    use FactFinderLogTrait;

    private ProductBundleService $productBundleService;

    /**
     * ProductBundleController constructor.
     *
     * @param ProductBundleService $productBundleService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductBundleService $productBundleService,
        LoggerInterface   $logger
    )
    {
        $this->productBundleService = $productBundleService;
        $this->logger = $logger;
    }

    /**
     * @Route("/widgets/ff/product-bundle/{type}", name="widgets.elio-ff.product-bundle.list", methods={"POST"}, defaults={"XmlHttpRequest"=true,"csrf_protected"=false})
     *
     * @param string $type
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function list(string $type, Request $request, SalesChannelContext $context): Response
    {
        try {
            $response = $this->renderStorefront('storefront/component/product/slider/default.html.twig', [
                'products' => $this->productBundleService->getProducts($type, $request, $context)
            ]);

            return $this->json([
                'success' => true,
                'data' => $response->getContent()
            ]);
        } catch (Throwable $e) {
            $this->ffError($e->getMessage(), $this, [$e]);
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
