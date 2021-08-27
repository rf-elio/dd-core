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

namespace Elio\FactFinder\Search;


use Elio\FactFinder\Api\Search\SearchApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingCollection;
use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\SearchRecord;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FactFinderSearchRoute
 * @package Elio\FactFinder\Search
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FactFinderSearchRoute extends AbstractProductSearchRoute
{
    private AbstractProductSearchRoute $decorated;
    private SearchRequestBuilder $searchRequestBuilder;
    private FactFinderConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private ProductListingLoader $productListingLoader;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * FactFinderSearchRoute constructor.
     * @param AbstractProductSearchRoute $decorated
     * @param SearchRequestBuilder $searchRequestBuilder
     * @param FactFinderConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductListingLoader $productListingLoader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        AbstractProductSearchRoute $decorated,
        SearchRequestBuilder $searchRequestBuilder,
        FactFinderConfigServiceInterface $configService,
        SearchApi $searchApi,
        ProductListingLoader $productListingLoader,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->decorated = $decorated;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->configService = $configService;
        $this->searchApi = $searchApi;
        $this->productListingLoader = $productListingLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $config = $this->configService->get($context->getSalesChannelId());
//$page = $this->getDecorated()->load($request, $context, $criteria);
        if(!$config->isSearchUseFactFinder()) {
            return $this->getDecorated()->load($request, $context, $criteria);
        }

        $searchRequest = $this->searchRequestBuilder->build($request, $criteria, $context);
        $searchResult = $this->searchApi->search($searchRequest, $context);

        // extract product ids
        $productNumbers = array_map(function (SearchRecord $searchRecord) {
            return $searchRecord->getId();
        }, $searchResult->getHits());

        $criteria->setIds([]);
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        $result = $this->productListingLoader->load($criteria, $context);

        $result = ProductListingResult::createFrom($result);
//        $result->setAvailableSortings(new ProductSortingCollection([
//            new ProductSortingEntity()
//        ]));
        $this->eventDispatcher->dispatch(
            new ProductSearchResultEvent($request, $result, $context),
            ProductEvents::PRODUCT_SEARCH_RESULT
        );

        $result->addCurrentFilter('search', $request->get('search'));

        return new ProductSearchRouteResponse($result);
    }
}