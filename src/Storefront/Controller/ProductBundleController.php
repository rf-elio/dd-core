<?php


namespace Elio\FactFinder\Storefront\Controller;


use Elio\FactFinder\Core\Logging\FactFinderLogTrait;
use Elio\FactFinder\Core\ProductBundle\ProductBundleInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
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
    private iterable $productBundles;

    /**
     * ProductBundleController constructor.
     *
     * @param iterable $productBundles
     * @param LoggerInterface $logger
     */
    public function __construct(
        iterable $productBundles,
        LoggerInterface $logger
    )
    {
        $this->productBundles = $productBundles;
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
            $productBundle = $this->getProductBundle($type);
            $view = $request->get('view', 'storefront/component/product/slider/default.html.twig');
            $viewParams = $request->get('viewParams', []);

            $response = $this->renderStorefront($view, $viewParams + [
                'products' => $productBundle->getProducts($request, $context)
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

    /**
     * @param string $type
     *
     * @return ProductBundleInterface
     */
    private function getProductBundle(string $type): ProductBundleInterface
    {
        /** @var ProductBundleInterface $productBundle */
        foreach ($this->productBundles as $productBundle) {
            if ($productBundle->supports($type)) {
                return $productBundle;
            }
        }
        throw new RuntimeException(sprintf('Product bundle with type "%s" does not exist', $type));
    }
}
