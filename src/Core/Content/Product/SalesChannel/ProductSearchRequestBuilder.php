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

namespace Elio\ElioSearch\Core\Content\Product\SalesChannel;


use Elio\ElioSearch\Api\Search\Request\ProductSearchRequest;
use Elio\ElioSearch\Api\Search\Request\SearchRequest;
use Elio\ElioSearch\Configuration\Configuration;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\Content\Product\SalesChannel\Event\ProductSearchRequestBuildedEvent;
use Elio\ElioSearch\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationExtension;
use Elio\ElioSearch\Core\Framework\DataAbstractionLayer\Search\AggregationResult\DefaultFacetExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProductSearchRequestBuilder
 * @package Elio\ElioSearch\Core\Content\Product\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ProductSearchRequestBuilder
{
    protected const PARAM_PAGE = 'p';
    protected const PARAM_SORT = 'order';
    protected const ANSWER_PATH_REQUEST_PARAM_PREFIX = 'elio-search-answer-path-';
    public const ADDITIONAL_REQUEST_PARAM_PREFIX = 'elio_search_additional_request_parameter_';

    /**
     * SearchRequestBuilder constructor.
     * @param ElioSearchConfigServiceInterface $configService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private readonly ElioSearchConfigServiceInterface $configService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * Builds the elio search search request
     * @param Request $request
     * @param Criteria $criteria
     * @param SalesChannelContext $salesChannelContext
     * @param ProductSearchRequest|null $searchRequest
     * @return ProductSearchRequest
     */
    public function build(
        Request               $request,
        Criteria              $criteria,
        SalesChannelContext   $salesChannelContext,
        ?ProductSearchRequest $searchRequest = null
    ) : ProductSearchRequest
    {
        $config = $this->configService->getByContext($salesChannelContext);
        $searchRequest ??= new ProductSearchRequest('');

        $payload = $request->query->all();
        if(!empty($request->get('search'))) {
            $searchRequest->setQuery($request->get('search'));
        }
        $this->addPage($payload, $searchRequest);
        $this->addSorting($payload, $searchRequest);
        $this->addFilters($payload, $searchRequest);
        $this->addCustomParameters($searchRequest, $config);
        $this->addAdditionalRequestParameters($payload, $searchRequest);

        $event = new ProductSearchRequestBuildedEvent($searchRequest, $payload);
        $this->eventDispatcher->dispatch($event);
        return $event->getSearchRequest();
    }

    /**
     * Adds the current page to the search request
     *
     * @param array $payload
     * @param ProductSearchRequest $searchRequest
     */
    protected function addPage(array $payload, ProductSearchRequest $searchRequest) : void
    {
        if(!isset($payload[self::PARAM_PAGE]) || empty($payload[self::PARAM_PAGE])) {
            return;
        }

        $page = (int)$payload[self::PARAM_PAGE];
        $page = max($page, 1);
        $searchRequest->setPage($page);
    }

    /**
     * Adds the applied sorting to the elio search request
     * @param array $payload
     * @param ProductSearchRequest $searchRequest
     */
    protected function addSorting(array $payload, ProductSearchRequest $searchRequest) : void
    {
        if(
            !isset($payload[self::PARAM_SORT]) ||
            empty($payload[self::PARAM_SORT]) ||
            !str_contains((string) $payload[self::PARAM_SORT], '.')
        ) {
            return;
        }

        $parts = explode('.', (string) $payload[self::PARAM_SORT]);
        $order = array_pop($parts);
        $field = implode('.', $parts);
        $searchRequest->setSort($field, $order);
    }

    /**
     * Adds the elio search filter to the search request
     *
     * @param array $payload
     * @param ProductSearchRequest $searchRequest
     */
    protected function addFilters(array $payload, ProductSearchRequest $searchRequest) : void
    {
         foreach ($payload as $key => $filterValues) {
             if(!str_starts_with($key, AggregationExtension::PARAMETER_NAME_PREFIX)) {
                 continue;
             }

             if (str_contains($key, 'default')) {
                 $filterValues = explode('|', (string) $filterValues);
                 foreach ($filterValues as $filterValue) {
                     [$name, $value] = DefaultFacetExtension::parseKey($filterValue);
                     $searchRequest->addFilter($name, $value);
                 }
             } elseif (str_contains($key, 'slider')) {
                 $filterValues = explode('|', (string) $filterValues);
                 foreach ($filterValues as $filterValue) {
                     [$name, $min, $max] = DefaultFacetExtension::parseKey($filterValue);
                     $searchRequest->addFilter($name, json_encode([(float)$min, (float)$max]));
                 }
             }elseif (str_contains($key, 'tree')){
                 $filterValues = explode('|', (string) $filterValues);
                 $filters = [];
                 foreach ($filterValues as $filterValue) {
                     [$name, $value] = DefaultFacetExtension::parseKey($filterValue);
                     if(!array_key_exists($name, $filters)){
                         $filters[$name] = [];
                     }
                     $filters[$name][] = $value;
                 }
                 foreach ($filters as $filtername => $filter){
                     $searchRequest->addFilter($filtername, $filter);
                 }
             }
         }
    }

    /**
     * Adds the additional request params to the elio search request
     *
     * @param SearchRequest $searchRequest
     * @param Configuration $config
     */
    protected function addCustomParameters(SearchRequest $searchRequest, Configuration $config) : void
    {
        $searchRequest->setAdditionalRequestParameters($config->getAdditionalRequestParameters());
    }

    /**
     * Adds additional parameters provided by the request
     *
     * @param array $payload
     *
     * @param ProductSearchRequest $searchRequest
     */
    protected function addAdditionalRequestParameters(array $payload, ProductSearchRequest $searchRequest) : void
    {
        $additionalParameters = $searchRequest->getAdditionalRequestParameters();
        foreach ($payload as $key => $value) {
            if (str_starts_with($key, self::ADDITIONAL_REQUEST_PARAM_PREFIX)) {
                $parameterName = str_replace(self::ADDITIONAL_REQUEST_PARAM_PREFIX, '', $key);
                $additionalParameters[$parameterName] = $value;
            }
        }

        $searchRequest->setAdditionalRequestParameters($additionalParameters);
    }
}
