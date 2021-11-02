<?php

namespace Elio\FactFinder\Core\Export;


/**
 * Class SeoRoute
 * @package Elio\FactFinder\Core\Export
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SeoRoute
{
    private string $routeName;
    private string $id;
    private array $parameters;
    private ?string $url = null;

    /**
     * @param string $routeName
     * @param string $id
     * @param array $parameters
     */
    public function __construct(
        string $routeName,
        string $id,
        array $parameters
    )
    {
        $this->id = $id;
        $this->routeName = $routeName;
        $this->parameters = $parameters;
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
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url ?? '';
    }

    /**
     * Checks if the url is already resolved
     *
     * @return bool
     */
    public function isResolved() : bool
    {
        return $this->url !== null;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function __toString()
    {
        return $this->url ?? '';
    }
}