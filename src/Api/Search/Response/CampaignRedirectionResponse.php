<?php

namespace Elio\ElioSearch\Api\Search\Response;


use Elio\ElioSearch\Api\Response\Response;

/**
 * Class CampaignRedirectionResponse
 * @package Elio\ElioSearch\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignRedirectionResponse extends Response
{
    private string $name;
    private string $targetUrl;

    /**
     * @param string $name
     * @param string $targetUrl
     */
    public function __construct(string $name, string $targetUrl)
    {
        $this->name = $name;
        $this->targetUrl = $targetUrl;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }
}