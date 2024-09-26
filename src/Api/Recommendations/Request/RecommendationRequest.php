<?php

namespace Elio\ElioDataDiscovery\Api\Recommendations\Request;

use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;

class RecommendationRequest extends ChannelRequest
{
    /**
     * @var string[]
     */
    private array $productIds;
    private int $limit;
    private ?string $sessionId = null;


    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * @param string|null $sessionId
     */
    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return string[]
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @param string[] $productIds
     */
    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }
}
