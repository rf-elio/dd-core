<?php

namespace Elio\ElioDataDiscovery\Api\Search\Response;


use Elio\ElioDataDiscovery\Api\Response\Response;
use Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel\ContentGroup;
use Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel\ContentItem;

/**
 * Class ContentListingResponse
 * @package Elio\ElioDataDiscovery\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentListingResponse extends Response
{
    /**
     * @var array<ContentItem>
     */
    protected array $contentItems = [];
    /**
     * @var array<ContentGroup>
     */
    protected array $contentGroups = [];
    /**
     * @var array<ContentGroup>
     */
    protected array $topContentGroups = [];

    /**
     * @param ContentItem $contentItem
     */
    public function addContentItem(ContentItem $contentItem) : void
    {
        $this->contentItems[] = $contentItem;
    }

    /**
     * @return ContentItem[]
     */
    public function getContentItems(): array
    {
        return $this->contentItems;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->contentGroups);
    }

    /**
     * @return ContentGroup[]
     */
    public function getContentGroups(): array
    {
        return $this->contentGroups;
    }

    /**
     * @param ContentGroup[] $contentGroups
     */
    public function setContentGroups(array $contentGroups): void
    {
        $this->contentGroups = $contentGroups;
    }

    /**
     * @return ContentGroup[]
     */
    public function getTopContentGroups(): array
    {
        return $this->topContentGroups;
    }

    /**
     * @param ContentGroup[] $topContentGroups
     */
    public function setTopContentGroups(array $topContentGroups): void
    {
        $this->topContentGroups = $topContentGroups;
    }
}