<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Sorting\Api\Controller;

use Elio\ElioSearch\Core\Sorting\ProductSortingService;
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
        path: '/api/_action/elio-search-product-sorting/{categoryId}/sync-products',
        name: 'api.action.elio_search_product_sorting.sync-products',
        methods: ['GET']
    )]
    public function syncProducts(string $categoryId): Response
    {
        if ($this->systemConfigService->get('ElioSearch.config.sortingLocation') === 'sortDisabled') {
            return new JsonResponse(['message' => 'elio-search.sort-positions.base.refreshIndex'], Response::HTTP_OK);
        }
        $this->productSortingService->removeProducts();
        $this->productSortingService->addProducts($categoryId);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
