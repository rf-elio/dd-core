<?php
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\FactFinder\Api\Search\Request;


use Elio\FactFinder\Api\Request\AbTestTrait;
use Elio\FactFinder\Api\Request\ChannelRequest;
use Elio\FactFinder\Api\Request\CustomParametersTrait;

/**
 * Class SearchRequest
 * @package Elio\FactFinder\Api\Request
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SearchRequest extends ChannelRequest
{
    use AbTestTrait;
    use CustomParametersTrait;

    protected string $query = '*';
    protected bool $excludeProductsNotInRange = true;
    protected int $page = 1;
    protected ?array $sort = null;
    protected array $filter = [];
    protected ?array $additionalRequestParameters = null;

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
     * Adds an filter to the ff search request
     *
     * @param string $name
     * @param string $value
     * @param bool $substring
     */
    public function addFilter(string $name, string $value, bool $substring = false) : void
    {
        if(!isset($this->filter[$name])) {
            $this->filter[$name] = [
                'values' => [],
                'substring' => $substring
            ];
        }
        $this->filter[$name]['values'][] = $value;
    }
}