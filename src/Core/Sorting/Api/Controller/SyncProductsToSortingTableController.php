<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sorting\Api\Controller;

use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigService;
use Elio\ElioDataDiscovery\Core\Sorting\ProductSortingService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class SyncProductsToSortingTableController extends AbstractController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly ProductSortingService $productSortingService
    ) {}

    #[Route(
        path: '/api/_action/elio-data-discovery-product-sorting/{categoryId}/sync-products',
        name: 'api.action.elio_data_discovery_product_sorting.sync-products',
        methods: ['GET']
    )]
    public function syncProducts(string $categoryId): Response
    {
        if ($this->systemConfigService->get(ElioDataDiscoveryConfigService::PLUGIN_CONFIG_PREFIX.'.sortingLocation') === 'sortDisabled') {
            return new JsonResponse(['message' => 'elio-data-discovery.sort-positions.info.sorting-disabled'], Response::HTTP_OK);
        }
        $this->productSortingService->removeProducts();
        $this->productSortingService->addProducts($categoryId);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
