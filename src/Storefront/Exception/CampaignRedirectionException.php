<?php

namespace Elio\ElioSearch\Storefront\Exception;


use Elio\ElioSearch\Api\Search\Response\CampaignRedirectionResponse;
use RuntimeException;

/**
 * Class CampaignRedirectionException
 * @package Elio\ElioSearch\Storefront\Exception
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignRedirectionException extends RuntimeException
{
    private CampaignRedirectionResponse $campaignRedirectionResponse;

    public function __construct(CampaignRedirectionResponse $campaignRedirectionResponse)
    {
        parent::__construct('Campaign redirection');
        $this->campaignRedirectionResponse = $campaignRedirectionResponse;
    }

    /**
     * @return CampaignRedirectionResponse
     */
    public function getCampaignRedirectionResponse(): CampaignRedirectionResponse
    {
        return $this->campaignRedirectionResponse;
    }
}