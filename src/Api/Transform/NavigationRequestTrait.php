<?php
declare(strict_types=1);

namespace Elio\FactFinder\Api\Transform;

use Elio\FactFinder\Api\Search\Request\NavigationRequest;

trait NavigationRequestTrait
{
    /**
     * @var NavigationRequest
     */
    protected NavigationRequest $navigationRequest;

    /**
     * @return NavigationRequest
     */
    public function getNavigationRequest(): NavigationRequest
    {
        return $this->navigationRequest;
    }

    /**
     * @param NavigationRequest $navigationRequest
     */
    public function setNavigationRequest(NavigationRequest $navigationRequest): void
    {
        $this->navigationRequest = $navigationRequest;
    }
}
