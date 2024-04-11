<?php

namespace Elio\ElioDataDiscovery\Api\Recommendations\Request;

use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;

class SimilarRequest extends ChannelRequest
{
    private string $productNumber;
    private int $limit;

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    /**
     * @param string $productNumber
     * @return void
     */
    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }
}
