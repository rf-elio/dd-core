<?php

namespace Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel;


use DateTimeInterface;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class ContentItem
 * @package Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentItem extends Struct
{
    /**
     * @param string $id
     * @param string $type
     * @param array $contentStructure
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $imageUrl
     * @param DateTimeInterface|null $publicationDate
     * @param int $priority
     * @param int $position
     */
    public function __construct(
        private readonly string             $id,
        private readonly string             $type,
        private readonly array              $contentStructure,
        private readonly string             $title,
        private readonly string             $description,
        private readonly string             $url,
        private readonly string             $imageUrl,
        private readonly ?DateTimeInterface $publicationDate,
        private readonly int                $priority,
        private readonly int                $position
    )
    {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getContentStructure(): array
    {
        return $this->contentStructure;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
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
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getPublicationDate(): ?DateTimeInterface
    {
        return $this->publicationDate;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    public function hasImageUrl(): bool
    {
        return !empty($this->getImageUrl());
    }
}