<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Recommendations\ResponseTransformer;

use Elio\ElioDataDiscovery\Api\Recommendations\Response\RecommendationResponse;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Api\Transform\ResponseTransformerInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractRecommendationProductTransformer implements ResponseTransformerInterface
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
     * @param array $productNumbersPerType
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     */
    public function loadProductsForTypes(
        array $productNumbersPerType,
        ResponseCollection  $responseCollection,
        SalesChannelContext $context,
    ): void
    {
        $allProductNumbers = array_unique(array_merge(...array_values($productNumbersPerType)));
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $allProductNumbers));
        /** @var ProductCollection $allProducts */
        $allProducts = $this->listingLoader->load($criteria, $context)->getEntities();

        $productNumbersToTypeMap = [];
        foreach ($productNumbersPerType as $type => $productNumbers) {
            foreach ($productNumbers as $productNumber) {
                $productNumbersToTypeMap[$productNumber][] = $type;
            }
        }

        $productsGroupedByType = [];
        foreach ($allProducts as $product) {
            $types = $productNumbersToTypeMap[$product->getProductNumber()];
            foreach ($types as $type) {
                if (!isset($productsGroupedByType[$type])) {
                    $productsGroupedByType[$type] = new ProductCollection();
                }
                $productsGroupedByType[$type]->add($product);
            }
        }

        foreach ($productsGroupedByType as $type => $products) {
            $productNumbers = $productNumbersPerType[$type];
            $productNumberSort = array_flip($productNumbers);

            $products->sort(static function (ProductEntity $a, ProductEntity $b) use ($productNumberSort) {
                $aPosition = $productNumberSort[$a->getProductNumber()];
                $bPosition = $productNumberSort[$b->getProductNumber()];

                if ($aPosition === $bPosition) {
                    return 0;
                }
                return ($aPosition < $bPosition) ? -1 : 1;
            });

            $response = $responseCollection->get(RecommendationResponse::class) ?? new RecommendationResponse();
            $listing = new ProductListingResponse();
            $listing->setCurrentPage(0);
            $listing->setHitsPerPage($products->count());
            $listing->setPageCount(1);
            $listing->setProducts($products);
            $response->setRecommendationType($type);
            $response->setProductListing($listing);
            $responseCollection->add($response);
        }
    }
}
