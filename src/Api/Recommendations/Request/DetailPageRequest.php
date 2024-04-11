<?php

namespace Elio\ElioDataDiscovery\Api\Recommendations\Request;

use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;

/**
 * Class DetailPageRequest
 *
 * @package Elio\ElioDataDiscovery\Api\Recommendations\Request
 */
class DetailPageRequest extends ChannelRequest
{
    private string $productNumber;
    private bool $withRecommendations = true;
    private bool $withSimilarProducts = true;
    private int $limitRecommendations = 0;
    private int $limitSimilarProducts = 10;
    private int $limitVariants = 5;

    /**
     * @return string
     */
    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    /**
     * @param string $productNumber
     * @return DetailPageRequest
     */
    public function setProductNumber(string $productNumber): self
    {
        $this->productNumber = $productNumber;
        return $this;
    }

    /**
     * @return bool
     */
    public function getWithRecommendations(): bool
    {
        return $this->withRecommendations;
    }

    /**
     * @param bool $withRecommendations
     * @return $this
     */
    public function setWithRecommendations(bool $withRecommendations): self
    {
        $this->withRecommendations = $withRecommendations;
        return $this;
    }

    /**
     * @return bool
     */
    public function getWithSimilarProducts(): bool
    {
        return $this->withSimilarProducts;
    }

    /**
     * @param bool $withSimilarProducts
     * @return $this
     */
    public function setWithSimilarProducts(bool $withSimilarProducts): self
    {
        $this->withSimilarProducts = $withSimilarProducts;
        return $this;
    }

    /**
     * @param int $limitRecommendations
     * @return $this
     */
    public function setLimitRecommendations(int $limitRecommendations): self
    {
        $this->limitRecommendations = $limitRecommendations;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimitRecommendations(): int
    {
        return $this->limitRecommendations;
    }

    /**
     * @param int $limitSimilarProducts
     * @return $this
     */
    public function setLimitSimilarProducts(int $limitSimilarProducts): self
    {
        $this->limitSimilarProducts = $limitSimilarProducts;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimitSimilarProducts(): int
    {
        return $this->limitSimilarProducts;
    }

    /**
     * @param int $limitVariants
     * @return $this
     */
    public function setLimitVariants(int $limitVariants): self
    {
        $this->limitVariants = $limitVariants;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimitVariants(): int
    {
        return $this->limitVariants;
    }
}
