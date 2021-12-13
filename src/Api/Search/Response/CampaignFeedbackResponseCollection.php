<?php

namespace Elio\FactFinder\Api\Search\Response;

use Elio\FactFinder\Api\Response\Response;

/**
 * Class CampaignFeedbackResponseCollection
 * @package Elio\FactFinder\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignFeedbackResponseCollection extends Response
{
    public const KEY = 'CampaignFeedbackResponseCollection';

    /**
     * @var CampaignFeedbackResponse[]
     */
    protected array $campaignFeedbackResponse = [];

    /**
     * @param CampaignFeedbackResponse $campaignFeedbackResponse
     */
    public function addCampaignFeedbackResponse(CampaignFeedbackResponse $campaignFeedbackResponse) : void
    {
        $this->campaignFeedbackResponse[] = $campaignFeedbackResponse;
    }

    /**
     * @return CampaignFeedbackResponse[]
     */
    public function getCampaignFeedbackResponse(): array
    {
        return $this->campaignFeedbackResponse;
    }

    /**
     * Filters the present textes by label
     *
     * @param string $label
     * @return CampaignFeedbackResponse[]
     */
    public function getByLabel(string $label) : array
    {
        $filtered = [];

        foreach ($this->campaignFeedbackResponse as $campaignFeedbackResponse) {
            if ($campaignFeedbackResponse->getLabel() === $label) {
                $filtered[] = $campaignFeedbackResponse;
            }
        }

        return $filtered;
    }
}