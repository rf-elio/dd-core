<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Sorting\Api\Controller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AddProductsToSortingTableController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    #[Route(path: '/api/add-products/{categoryId}', name: 'api.custom.elio_search_product_sorting.add-products', methods: ['GET'])]
    public function addProducts(string $categoryId): Response
    {
        $sql = 'SELECT MAX(position) FROM elio_search_product_sorting WHERE category_id = ?';
        $maxPosition = $this->connection->fetchOne($sql, [Uuid::fromHexToBytes($categoryId)]) ?? 0;

        $sql = 'INSERT INTO elio_search_product_sorting (id, product_id, product_version_id, category_id, category_version_id, position, created_at )
                SELECT UNHEX(MD5(CONCAT(pc.product_id, pc.category_id))) AS id, pc.product_id, pc.product_version_id, pc.category_id, pc.category_version_id, ? + ROW_NUMBER() over () AS position, NOW()
                FROM (SELECT @row_number:=0) AS t, product_category AS pc
                LEFT JOIN elio_search_product_sorting esps ON esps.product_id = pc.product_id AND esps.category_id = pc.category_id
                WHERE pc.category_id = ? AND esps.id IS NULL';

        $this->connection->executeStatement($sql, [$maxPosition, Uuid::fromHexToBytes($categoryId)]);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
