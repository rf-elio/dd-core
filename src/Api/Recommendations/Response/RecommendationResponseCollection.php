<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Recommendations\Response;

class RecommendationResponseCollection
{
    public const KEY = "RecommendationResponseCollection";

    /**
     * @var RecommendationResponse[]
     */
    protected array $recommendationResponses = [];

    public function getRecommendationResponses(): array
    {
        return $this->recommendationResponses;
    }

    public function addRecommendationResponse(RecommendationResponse $recommendationResponse): void
    {
        $this->recommendationResponses[] = $recommendationResponse;
    }
}
