<?php

namespace Elio\ElioSearch\Api\Search\Request;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class AdvisorStatus
 * @package Elio\ElioSearch\Api\Search\Request
 * @author Ralf Frommherz
 */
class AdvisorStatus extends Struct
{
    /**
     * @param string $answerPath
     * @param string $campaignId
     */
    public function __construct(
        private readonly string $answerPath,
        private readonly string $campaignId
    ) {}

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
