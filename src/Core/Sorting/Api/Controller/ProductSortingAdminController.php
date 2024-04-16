<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sorting\Api\Controller;

use Elio\ElioDataDiscovery\Core\Sorting\ProductSortingService;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ProductSortingAdminController extends AbstractController
{
    public function __construct(
        private readonly ProductSortingService $productSortingService
    ) {}

    #[Route(path:'/api/_action/elio-data-discovery/refresh-index', name: 'api.custom.elio_data_discovery_product_sorting.refresh-index', methods: ['GET'] )]
    public function refreshIndex(): Response
    {
        $this->productSortingService->sort(Context::createDefaultContext());
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
