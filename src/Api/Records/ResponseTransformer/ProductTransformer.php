<?php


namespace Elio\FactFinder\Api\Records\ResponseTransformer;


use Elio\FactFinder\Api\Records\Response\ProductsResponse;
use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\RecommendationResultWithFieldRoles;
use Swagger\Client\Model\SimilarProductsWithFieldRoles;
use Swagger\Client\Model\TypedFlatRecord;

/**
 * Class ProductTransformer
 *
 * @package Elio\FactFinder\Api\Records\ResponseTransformer
 */
class ProductTransformer implements ResponseTransformerInterface
{
    private ProductListingLoader $listingLoader;

    /**
     * ProductTransformer constructor.
     *
     * @param ProductListingLoader $listingLoader
     */
    public function __construct(ProductListingLoader $listingLoader)
    {
        $this->listingLoader = $listingLoader;
    }

    /**
     * @param ModelInterface $model
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     *
     * @return bool
     */
    public function supports(ModelInterface $model, ApiRequest $request, SalesChannelContext $context): bool
    {
        return $model instanceof RecommendationResultWithFieldRoles || $model instanceof SimilarProductsWithFieldRoles;
    }

    /**
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(
        ModelInterface $model,
        ResponseCollection $responseCollection,
        SalesChannelContext $context,
        ApiRequest $request
    ): void {
        $productNumbers = $this->extractProductNumbers($model);
        $productNumberSort = array_flip($productNumbers);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        /** @var ProductCollection $products */
        $products = $this->listingLoader->load($criteria, $context)->getEntities();

        // sorts the product collection based on the original ff result order
        $products->sort(static function (ProductEntity $a, ProductEntity $b) use ($productNumberSort) {
            $aPosition = $productNumberSort[$a->getProductNumber()];
            $bPosition = $productNumberSort[$b->getProductNumber()];

            if ($aPosition === $bPosition) {
                return 0;
            }
            return ($aPosition < $bPosition) ? -1 : 1;
        });

        $listing = $responseCollection->get(ProductsResponse::class) ?? new ProductsResponse();
        $responseCollection->set(ProductsResponse::class, $listing);
        $listing->setProducts($products);
    }

    /**
     * @param ModelInterface $result
     *
     * @return array
     */
    protected function extractProductNumbers(ModelInterface $result): array
    {
        if (!$result instanceof RecommendationResultWithFieldRoles && !$result instanceof SimilarProductsWithFieldRoles) {
            throw new InvalidTypeException(
                $result,
                sprintf('%s or %s', RecommendationResultWithFieldRoles::class, SimilarProductsWithFieldRoles::class)
            );
        }

        return array_map(static function (TypedFlatRecord $record) {
            return $record->getId();
        }, $result->getHits());
    }
}
