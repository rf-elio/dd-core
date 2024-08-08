<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sync;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Uuid\Uuid;

class RatingCountService
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    public function updateProductRatingCounts(Context $context, array $productIds): void
    {
        $versionId = Uuid::fromHexToBytes($context->getVersionId());

        $query = $this->connection->createQueryBuilder();
        $subQuery = $this->connection->createQueryBuilder();
        $subQuery->select(
            'IFNULL(product.parent_id, pr.product_id) AS id',
            'COUNT(pr.id) AS review_count'
        );
        $subQuery->from('product_review', 'pr');
        $subQuery->leftJoin('pr', 'product', 'product', 'product.id = pr.product_id');
        $subQuery->andWhere('pr.status = 1');
        $subQuery->groupBy('id');

        $query->select(
            'DISTINCT pt.product_id',
            'pt.custom_fields',
            'COALESCE(sq.review_count, 0) AS review_count'
        );
        $query->from('product_translation', 'pt');
        $query->leftJoin('pt', '(' . $subQuery->getSQL() . ')', 'sq', 'pt.product_id = sq.id');
        $query->andWhere('pt.product_id IN (:productIds)');
        $query->andWhere('pt.product_version_id = :version');
        $query->setParameter('version', $versionId);
        $query->setParameter('productIds', Uuid::fromHexToBytesList($productIds), ArrayParameterType::BINARY);

        $products = $query->executeQuery()->fetchAllAssociative();

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('
                UPDATE product_translation
                SET custom_fields = JSON_SET(IFNULL(custom_fields, "{}"), "$.ElioDataDiscoveryProduct_elio_data_discovery_product_rating_count", :reviews),
                    updated_at = :now
                WHERE product_id = :id AND product_version_id = :version
            ')
        );

        foreach ($products as $product) {
            $query->execute([
                'reviews' => $product['review_count'],
                'id' => $product['product_id'],
                'version' => $versionId,
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]);
        }
    }
}
