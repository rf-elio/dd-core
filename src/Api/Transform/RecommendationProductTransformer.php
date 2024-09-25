<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Transform;

use Elio\ElioBatteryIncludedApiClient\Model\RecommendationResultCollection;
use Elio\ElioBatteryIncludedSearchExtension\Api\Recommendations\Util\ProductNumberExtractor;
use Elio\ElioDataDiscovery\Api\Recommendations\Response\RecommendationResponse;
use Elio\ElioDataDiscovery\Api\Request\ApiRequest;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Api\Search\ResponseTransformer\AbstractProductTransformer;
use Elio\ElioDataDiscovery\Core\Exception\InvalidTypeException;
use Elio\ElioDataDiscovery\Swagger\ModelInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class RecommendationProductTransformer implements ResponseTransformerInterface
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
        return $model instanceof RecommendationResultCollection;
    }

    /**
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(
        ModelInterface      $model,
        ResponseCollection  $responseCollection,
        SalesChannelContext $context,
        ApiRequest          $request
    ): void
    {
        if (!$model instanceof RecommendationResultCollection) {
            throw new InvalidTypeException($model, RecommendationResultCollection::class);
        }

        $productNumbersPerType = ProductNumberExtractor::extractProductNumbers($model);
        $productNumbersPerType["together"] = ["SW10001", "SW10002"];
        foreach ($productNumbersPerType as $type => $productNumbers) {
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

//        $response = $responseCollection->get(RecommendationResponse::class) ?? new RecommendationResponse();
//        $responseCollection->set(RecommendationResponse::class, $response);
//        $response->setProducts($products);
//        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
//        $responseCollection->set(ProductListingResponse::class, $listing);
//        $listing->setCurrentPage(0);
//        $listing->setHitsPerPage($products->count());
//        $listing->setPageCount(1);
//        $listing->setProducts($products);
        //dd($responseCollection);
    }
}
