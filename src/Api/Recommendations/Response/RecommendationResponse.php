<?php

namespace Elio\ElioDataDiscovery\Api\Recommendations\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;

class RecommendationResponse extends Response
{
    protected string $recommendationType;
    protected ProductListingResponse $productListing;

    public function getProductListing(): ProductListingResponse
    {
        return $this->productListing;
    }

    public function setProductListing(ProductListingResponse $productListing): void
    {
        $this->productListing = $productListing;
    }

    public function getRecommendationType(): string
    {
        return $this->recommendationType;
    }

    public function setRecommendationType(string $recommendationType): void
    {
        $this->recommendationType = $recommendationType;
    }
}
