<?php

namespace Elio\ElioSearch\Api\Search\Request;

/**
 * Class AdvisorStatus
 * @package Elio\ElioSearch\Api\Search\Request
 * @author Ralf Frommherz
 */
class AdvisorStatus
{
    private string $answerPath;
    private string $campaignId;

    /**
     * @param string $answerPath
     * @param string $campaignId
     */
    public function __construct(string $answerPath, string $campaignId)
    {
        $this->answerPath = $answerPath;
        $this->campaignId = $campaignId;
    }

    /**
     * @return string
     */
    public function getAnswerPath(): string
    {
        return $this->answerPath;
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }
}
