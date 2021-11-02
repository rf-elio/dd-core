<?php

namespace Elio\FactFinder\Core\Content\Content\SalesChannel;


use DateTimeInterface;

/**
 * Class ContentItem
 * @package Elio\FactFinder\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentItem
{
    private string $id;
    private string $type;
    private string $contentStructure;
    private string $title;
    private string $description;
    private string $url;
    private string $imageUrl;
    private ?DateTimeInterface $publicationDate;
    private int $priority;
    private int $position;

    /**
     * @param string $id
     * @param string $type
     * @param string $contentStructure
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $imageUrl
     * @param DateTimeInterface|null $publicationDate
     * @param int $priority
     * @param int $position
     */
    public function __construct(
        string             $id,
        string             $type,
        string             $contentStructure,
        string             $title,
        string             $description,
        string             $url,
        string             $imageUrl,
        ?DateTimeInterface $publicationDate,
        int                $priority,
        int                $position
    )
    {
        $this->id = $id;
        $this->type = $type;
        $this->contentStructure = $contentStructure;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->publicationDate = $publicationDate;
        $this->priority = $priority;
        $this->position = $position;
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
     * @return string
     */
    public function getContentStructure(): string
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
}