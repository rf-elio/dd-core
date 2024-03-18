<?php

namespace Elio\ElioDataDiscovery\Api\Search\Response;


use Elio\ElioDataDiscovery\Api\Response\Response;

/**
 * Class CampaignFeedbackResponse
 * @package Elio\ElioDataDiscovery\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignFeedbackResponse extends Response
{
    /**
     * @param string $label
     * @param string $text
     * @param bool $html
     */
    public function __construct(
        private readonly string $label,
        private readonly string $text,
        private readonly bool $html
    ) {}

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return bool
     */
    public function isHtml(): bool
    {
        return $this->html;
    }
}