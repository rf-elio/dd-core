<?php


namespace Elio\FactFinder\Storefront\Controller;


use Elio\FactFinder\Core\ProductBundle\ProductBundleInterface;
use RuntimeException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 * Class ProductBundleController
 *
 * @package Elio\FactFinder\Storefront\Controller
 */
class ProductBundleController extends StorefrontController
{
    private iterable $productBundles;

    public function __construct(
        iterable $productBundles
    )
    {
        $this->productBundles = $productBundles;
    }

    /**
     * @Route("/widgets/ff/product-bundle/{type}", name="widgets.elio-ff.product-bundle.list", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * @param string $type
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function list(string $type, Request $request, SalesChannelContext $context): Response
    {
        $productBundle = $this->getProductBundle($type);
        $productBundle->getProducts($request, $context);
        $view = $request->get('view', 'defaultView');

        return $this->render($view, [
            'products' => $productBundle->getProducts($request, $context)
        ]);
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
