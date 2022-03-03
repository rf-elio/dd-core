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

namespace Elio\FactFinder\Api\Search;


use Elio\FactFinder\Api\ApiClientFactoryInterface;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Request\ContentSearchRequest;
use Elio\FactFinder\Api\Search\Request\NavigationRequestProduct;
use Elio\FactFinder\Api\Search\Request\ProductSearchRequest;
use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Elio\FactFinder\Api\Transform\Transformer;
use Elio\FactFinder\Core\Logging\FactFinderLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Swagger\Client\Model\SortItem;
use Throwable;

/**
 * Class SearchApi
 * @package Elio\FactFinder\Api\Search
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SearchApi
{
    use FactFinderLogTrait;
    private ApiClientFactoryInterface $apiFactory;
    private Transformer $transformer;

    /**
     * SearchApi constructor.
     * @param ApiClientFactoryInterface $apiFactory
     * @param Transformer $transformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClientFactoryInterface $apiFactory,
        Transformer $transformer,
        LoggerInterface $logger
    )
    {
        $this->apiFactory = $apiFactory;
        $this->transformer = $transformer;
        $this->logger = $logger;
    }

    /**
     * Executes the ff search request
     *
     * @param ProductSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function search(ProductSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $this->ffDebug('search', $this, [$searchRequest, $context]);

        $params = [
            'query' => $searchRequest->getQuery(),
            'channel' => $searchRequest->getChannel(),
            'sortItems' => $this->getSorting($searchRequest),
            'page' => $searchRequest->getPage(),
            'customParameters' => $this->getCustomParameters($searchRequest),
            'filters' => $this->getFilters($searchRequest)
        ];

        if ($searchRequest->getAdvisorStatus() !== null) {
            $params['advisorStatus'] = [
                'answerPath' => $searchRequest->getAdvisorStatus()->getAnswerPath(),
                'id' => $searchRequest->getAdvisorStatus()->getId()
            ];
        }

        if ($searchRequest->getHitsPerPage() !== null) {
            $params['hitsPerPage'] = $searchRequest->getHitsPerPage();
        }

        $apiClient = $this->apiFactory->createSearchApi($context);
        $result = $apiClient->searchUsingPOST(new \Swagger\Client\Model\SearchRequest(['params' => $params]));
        return $this->transformer->transformResponse($result, $context, $searchRequest);
    }

    /**
     * @param ContentSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function searchContent(ContentSearchRequest $searchRequest, SalesChannelContext $context) : ResponseCollection
    {
        $apiClient = $this->apiFactory->createSearchApi($context);
        $result = $apiClient->searchUsingPOST(new \Swagger\Client\Model\SearchRequest(['params' => [
            'query' => $searchRequest->getQuery(),
            'channel' => $searchRequest->getChannel(),
            'sortItems' => $this->getSorting($searchRequest),
            'page' => $searchRequest->getPage(),
            'customParameters' => $this->getCustomParameters($searchRequest),
            'filters' => $this->getFilters($searchRequest)
        ]]));
        return $this->transformer->transformResponse($result, $context, $searchRequest);
    }

    /**
     * Executes the ff navigation request
     *
     * @param NavigationRequestProduct $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function navigation(NavigationRequestProduct $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createSearchApi($context);
        $filters = $this->getNavigationFilters($searchRequest);
        $params = [
            'channel' => $searchRequest->getChannel(),
            'sortItems' => $this->getSorting($searchRequest),
            'page' => $searchRequest->getPage(),
            'customParameters' => $this->getCustomParameters($searchRequest),
            'filters' => $filters
        ];
        if ($searchRequest->getAdvisorStatus() !== null) {
            $params['advisorStatus'] = [
                'answerPath' => $searchRequest->getAdvisorStatus()->getAnswerPath(),
                'id' => $searchRequest->getAdvisorStatus()->getId()
            ];
        }
        $result = $apiClient->navigationUsingPOST(new \Swagger\Client\Model\NavigationRequest([
            'params' => $params
        ]));
        return $this->transformer->transformResponse($result, $context, $searchRequest);
    }

    /**
     * Converts the sortings to SortItem's
     *
     * @param SearchRequest $searchRequest
     * @return array|SortItem[]
     */
    protected function getSorting(SearchRequest $searchRequest): array
    {
        if(!$searchRequest->getSort()) {
            return [];
        }

        return [
            new SortItem([
                'name' => $searchRequest->getSort()['name'],
                'order' => $searchRequest->getSort()['order']
            ])
        ];
    }

    /**
     * Prepares the filters to match the ff request structure
     *
     * @param SearchRequest $searchRequest
     * @return array
     */
    protected function getFilters(SearchRequest $searchRequest) : array
    {
        $preparedFilters = [];
        foreach ($searchRequest->getFilter() as $name => $filter) {
            $preparedFilter = [
                'name' => $name,
                'substring' => $filter['substring'],
                'values' => [],
            ];

            foreach ($filter['values'] as $value) {
                $preparedFilter['values'][] = [
                    'exclude' => false,
                    'type' => 'or',
                    'value' => $value
                ];
            }

            $preparedFilters[] = $preparedFilter;
        }

        return $preparedFilters;
    }

    /**
     * Fetching filters from NavigationRequest
     *
     * @param NavigationRequestProduct $navigationRequest
     * @return array
     */
    protected function getNavigationFilters(NavigationRequestProduct $navigationRequest): array
    {
        $filters = $this->getFilters($navigationRequest);
        if(!empty($navigationRequest->getCategoryPath())){
            $preparedFilter = [
                'name' => 'CategoryPath',
                'substring' => false,
                'values' => [[
                    'exclude' => false,
                    'type' => 'or',
                    'value' => explode('/', $navigationRequest->getCategoryPath())
                ]],
            ];
            $filters[] = $preparedFilter;
        }
        return $filters;
    }

    /**
     * Creates the custom params based on the additional parameters
     *
     * @param SearchRequest $searchRequest
     * @return array
     */
    protected function getCustomParameters(SearchRequest $searchRequest): array
    {
        if(!$searchRequest->getAdditionalRequestParameters()) {
            return [];
        }

        $customParameters = [];

        foreach ($searchRequest->getAdditionalRequestParameters() as $name => $value) {
            $customParameters[] = [
                'cacheIrrelevant' => true,
                'name' => $name,
                'values' => [$value]
            ];
        }

        return $customParameters;
    }
}
