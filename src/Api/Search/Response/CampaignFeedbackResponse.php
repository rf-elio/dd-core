<?php

namespace Elio\ElioSearch\Api\Search\Response;


use Elio\ElioSearch\Api\Response\Response;

/**
 * Class CampaignFeedbackResponse
 * @package Elio\ElioSearch\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignFeedbackResponse extends Response
{
    private string $label;
    private string $text;
    private bool $html;

    /**
     * @param string $label
     * @param string $text
     * @param bool $html
     */
    public function __construct(string $label, string $text, bool $html)
    {
        $this->label = $label;
        $this->text = $text;
        $this->html = $html;
    }

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