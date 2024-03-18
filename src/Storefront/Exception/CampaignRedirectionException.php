<?php

namespace Elio\ElioDataDiscovery\Storefront\Exception;


use Elio\ElioDataDiscovery\Api\Search\Response\CampaignRedirectionResponse;
use RuntimeException;

/**
 * Class CampaignRedirectionException
 * @package Elio\ElioDataDiscovery\Storefront\Exception
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignRedirectionException extends RuntimeException
{
    public function __construct(
        private readonly CampaignRedirectionResponse $campaignRedirectionResponse
    )
    {
        parent::__construct('Campaign redirection');
    }

    /**
     * @return CampaignRedirectionResponse
     */
    public function getCampaignRedirectionResponse(): CampaignRedirectionResponse
    {
        return $this->campaignRedirectionResponse;
    }
}