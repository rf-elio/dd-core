<?php

namespace Elio\ElioDataDiscovery\Api\Search\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;

class AdvisorCampaignResponseCollection extends Response
{
    public const KEY = 'AdvisorCampaignResponseCollection';

    /**
     * @var AdvisorCampaignResponse[]
     */
    protected array $advisorCampaignResponses = [];

    /**
     * @param AdvisorCampaignResponse $advisorResponse
     *
     * @return void
     */
    public function addAdvisorCampaignResponse(AdvisorCampaignResponse $advisorResponse): void
    {
        $this->advisorCampaignResponses[] = $advisorResponse;
    }

    /**
     * @return AdvisorCampaignResponse[]
     */
    public function getAdvisorCampaignResponses(): array
    {
        return $this->advisorCampaignResponses;
    }
}
