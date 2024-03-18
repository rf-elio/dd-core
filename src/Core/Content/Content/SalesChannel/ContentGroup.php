<?php

namespace Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel;


/**
 * Class ContentGroup
 * @package Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentGroup
{
    /**
     * @var ContentItem[]
     */
    protected array $contentItems = [];

    /**
     * @param string $type
     * @param string $label
     */
    public function __construct(
        private readonly string $type,
        private readonly string $label
    ) {}

    /**
     * @param ContentItem $contentItem
     */
    public function addContentItem(ContentItem $contentItem) : void
    {
        $this->contentItems[] = $contentItem;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return ContentItem[]
     */
    public function getContentItems(): array
    {
        return $this->contentItems;
    }
}