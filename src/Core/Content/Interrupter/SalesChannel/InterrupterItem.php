<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Interrupter\SalesChannel;

use Shopware\Core\Framework\Struct\Struct;

class InterrupterItem extends Struct
{
    public const INTERRUPTER_ITEM_TYPE = 'teaser';

    /**
     * @param string $name
     * @param int $position
     * @param string $format
     * @param string $url
     * @param string $imageDesktop
     * @param string $imageMobile
     * @param string $alt
     * @param string $html
     * @param string $itemId
     * @param string $itemType
     */
    public function __construct(
        private readonly string $name,
        private readonly int $position,
        private readonly string $format,
        private readonly string $url,
        private readonly string $imageDesktop,
        private readonly string $imageMobile,
        private readonly string $alt,
        private readonly string $html,
        private string $itemId,
        private readonly string $itemType
    ) {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getImageDesktop(): string
    {
        return $this->imageDesktop;
    }

    /**
     * @return string
     */
    public function getImageMobile(): string
    {
        return $this->imageMobile;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     */
    public function setItemId(string $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return string
     */
    public function getItemType(): string
    {
        return $this->itemType;
    }
}
