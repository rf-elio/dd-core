<?php

namespace Elio\FactFinder\Api\Search\Response;

use Elio\FactFinder\Api\Response\Response;

class AdvisorCampaignResponseCollection extends Response
{
    public const KEY = 'AdvisorCampaignResponseCollection';

    /**
     * @var AdvisorCampaignResponse[]
     */
    protected array $advisorCampaignResponse = [];

    /**
     * @param AdvisorCampaignResponse $advisorResponse
     *
     * @return void
     */
    public function addAdvisorCampaignResponse(AdvisorCampaignResponse $advisorResponse): void
    {
        $this->advisorCampaignResponse[] = $advisorResponse;
    }

    /**
     * @return AdvisorCampaignResponse[]
     */
    public function getAdvisorCampaignResponse(): array
    {
        return $this->advisorCampaignResponse;
    }

    /**
     * @param string|null $name
     *
     * @return AdvisorCampaignResponse|null
     */
    public function getByName(?string $name): ?AdvisorCampaignResponse
    {
        foreach ($this->advisorCampaignResponse as $advisorResponse) {
            if ($advisorResponse->getName() === $name) {
                return $advisorResponse;
            }
        }
        return null;
    }

    /**
     * @return AdvisorCampaignResponse|null
     */
    public function getFirstCampaign(): ?AdvisorCampaignResponse
    {
        return $this->advisorCampaignResponse[0] ?? null;
    }
}
