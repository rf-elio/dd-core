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

namespace Elio\ElioSearch\Api\Records;


use Elio\ElioSearch\Api\ApiClientFactoryInterface;
use Elio\ElioSearch\Api\Records\Request\DetailPageRequest;
use Elio\ElioSearch\Api\Records\Request\RecommendationRequest;
use Elio\ElioSearch\Api\Records\Request\RecordRequest;
use Elio\ElioSearch\Api\Records\Request\SimilarRequest;
use Elio\ElioSearch\Api\Response\ResponseCollection;
use Elio\ElioSearch\Api\Transform\Transformer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Swagger\Client\Model\FullRecordsResult;
use Throwable;

/**
 * Class RecordsApi
 *
 * @package Elio\ElioSearch\Api\Records
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class RecordsApi
{
    private ApiClientFactoryInterface $apiFactory;
    private Transformer $transformer;

    /**
     * RecordsApi constructor.
     *
     * @param ApiClientFactoryInterface $apiFactory
     * @param Transformer $transformer
     */
    public function __construct(
        ApiClientFactoryInterface $apiFactory,
        Transformer $transformer
    )
    {
        $this->apiFactory = $apiFactory;
        $this->transformer = $transformer;
    }

    /**
     * @param RecordRequest $request
     * @param SalesChannelContext $context
     *
     * @return FullRecordsResult
     * @throws ApiException
     */
    public function getRecords(RecordRequest $request, SalesChannelContext $context): FullRecordsResult
    {
        $apiClient = $this->apiFactory->createRecordsApi($context);
        return $apiClient->getFullRecordsUsingGET($request->getChannel(), [$request->getId()], null, 'productNumber');
    }

    /**
     * @param DetailPageRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function getDetailPage(DetailPageRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createRecordsApi($context);
        $result = $apiClient->getDetailPageUsingGET(
            $request->getChannel(),
            $request->getId(),
            $request->getIdType(),
            $request->getIdsOnly(),
            $request->getMaxResultsRecommendations(),
            $request->getMaxResultsSimilarProducts(),
            $request->getUsePersonalization(),
            $request->getSessionId(),
            $request->getPurchaserId(),
            $request->getLatitude(),
            $request->getLongitude(),
            $request->getMarketIds(),
            $request->getMaxCountVariants(),
            $request->getWithCampaigns(),
            $request->getWithRecommendations(),
            $request->getWithSimilarProducts(),
            $request->getWithRecord()
        );
        return $this->transformer->transformResponse($result, $context, $request);
    }

    /**
     * @param RecommendationRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function getRecommendations(RecommendationRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createRecordsApi($context);
        $result = $apiClient->getRecommendationUsingGET(
            $request->getChannel(),
            $request->getIds(),
            $request->getMaxResults(),
            $request->getSessionId(),
            null,
            true
        );

        return $this->transformer->transformResponse($result, $context, $request);
    }

    /**
     * @param SimilarRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function getSimilar(SimilarRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createRecordsApi($context);
        $result = $apiClient->getSimilarProductsUsingGET(
            $request->getChannel(),
            $request->getId(),
            $request->getIdType(),
            null,
            true,
            null,
            $request->getMaxResults()
        );

        return $this->transformer->transformResponse($result, $context, $request);
    }
}
