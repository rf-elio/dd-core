<?php

namespace Elio\ElioDataDiscovery\Api\Search\Request;


use Elio\ElioDataDiscovery\Api\Request\AbTestTrait;
use Elio\ElioDataDiscovery\Api\Request\ChannelRequest;
use Elio\ElioDataDiscovery\Api\Request\CustomParametersTrait;

/**
 * Class SearchRequest
 * @package Elio\ElioDataDiscovery\Api\Search\Request
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
abstract class SearchRequest extends ChannelRequest
{
    use AbTestTrait;
    use CustomParametersTrait;

    protected string $query = '*';
    protected int $page = 1;
    protected ?array $sort = null;
    protected array $filter = [];
    protected ?array $additionalRequestParameters = null;
    protected ?int $hitsPerPage = null;

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * @return array|null
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * @param string $name
     * @param string $order
     */
    public function setSort(string $name, string $order): void
    {
        $this->sort = [
            'name' => $name,
            'order' => $order
        ];
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return array|null
     */
    public function getAdditionalRequestParameters(): ?array
    {
        return $this->additionalRequestParameters;
    }

    /**
     * @param array|null $additionalRequestParameters
     */
    public function setAdditionalRequestParameters(?array $additionalRequestParameters): void
    {
        $this->additionalRequestParameters = $additionalRequestParameters;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Adds an filter to the elio search search request
     *
     * @param string $name
     * @param array|string $value
     * @param bool $substring
     */
    public function addFilter(string $name, array|string $value, bool $substring = false) : void
    {
        if (!isset($this->filter[$name])) {
            $this->filter[$name] = [
                'values' => [],
                'substring' => $substring
            ];
        }
        $this->filter[$name]['values'][] = $value;
    }

    /**
     * @return int|null
     */
    public function getHitsPerPage(): ?int
    {
        return $this->hitsPerPage;
    }

    /**
     * @param int|null $hitsPerPage
     */
    public function setHitsPerPage(?int $hitsPerPage): void
    {
        $this->hitsPerPage = $hitsPerPage;
    }
}
