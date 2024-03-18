<?php declare(strict_types=1);
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

namespace Elio\ElioDataDiscovery\Api\Search\Response;


use Elio\ElioDataDiscovery\Api\Response\Response;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingCollection;
use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;

/**
 * Class ProductListingResponse
 * @package Elio\ElioDataDiscovery\Api\Search\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ProductListingResponse extends Response
{
    protected int $totalHits = 0;
    protected int $currentPage = 0;
    protected int $hitsPerPage = 0;
    protected int $pageCount = 0;
    protected ?ProductSortingCollection $availableSortings = null;
    protected ?ProductSortingEntity $currentSorting = null;
    protected ?ProductCollection $products = null;
    protected ?AggregationResultCollection $aggregations = null;

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getHitsPerPage(): int
    {
        return $this->hitsPerPage;
    }

    /**
     * @param int $hitsPerPage
     */
    public function setHitsPerPage(int $hitsPerPage): void
    {
        $this->hitsPerPage = $hitsPerPage;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * @param int $pageCount
     */
    public function setPageCount(int $pageCount): void
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @param int $totalHits
     * @return ProductListingResponse
     */
    public function setTotalHits(int $totalHits): ProductListingResponse
    {
        $this->totalHits = $totalHits;
        return $this;
    }

    /**
     * @return ProductSortingCollection|null
     */
    public function getAvailableSortings(): ?ProductSortingCollection
    {
        return $this->availableSortings;
    }

    /**
     * @param ProductSortingCollection|null $availableSortings
     */
    public function setAvailableSortings(?ProductSortingCollection $availableSortings): void
    {
        $this->availableSortings = $availableSortings;
    }

    /**
     * @return ProductCollection|null
     */
    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    /**
     * @param ProductCollection|null $products
     */
    public function setProducts(?ProductCollection $products): void
    {
        $this->products = $products;
    }

    /**
     * @return int
     */
    public function getTotalHits(): int
    {
        return $this->totalHits;
    }

    /**
     * @return ProductSortingEntity|null
     */
    public function getCurrentSorting(): ?ProductSortingEntity
    {
        return $this->currentSorting;
    }

    /**
     * @param ProductSortingEntity|null $currentSorting
     */
    public function setCurrentSorting(?ProductSortingEntity $currentSorting): void
    {
        $this->currentSorting = $currentSorting;
    }

    /**
     * @return AggregationResultCollection|null
     */
    public function getAggregations(): ?AggregationResultCollection
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationResultCollection|null $aggregations
     */
    public function setAggregations(?AggregationResultCollection $aggregations): void
    {
        $this->aggregations = $aggregations;
    }
}