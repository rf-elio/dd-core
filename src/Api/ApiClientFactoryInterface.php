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

namespace Elio\FactFinder\Api;


use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Api\CampaignApi;
use Swagger\Client\Api\ImportApi;
use Swagger\Client\Api\ManagementApi;
use Swagger\Client\Api\PredbasketApi;
use Swagger\Client\Api\RecordsApi;
use Swagger\Client\Api\SearchApi;
use Swagger\Client\Api\TrackingApi;

/**
 * Interface ApiClientFactoryInterface
 * @package Elio\FactFinder\Api
 */
interface ApiClientFactoryInterface
{
    /**
     * Creates the campaign api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return CampaignApi
     */
    public function createCampaignApi(SalesChannelContext $salesChannelContext) : CampaignApi;

    /**
     * Creates the import api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return ImportApi
     */
    public function createImportApi(SalesChannelContext $salesChannelContext) : ImportApi;

    /**
     * Creates the management api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return ManagementApi
     */
    public function createManagementApi(SalesChannelContext $salesChannelContext) : ManagementApi;

    /**
     * Creates the predictive basket api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return PredbasketApi
     */
    public function createPredictiveBasketApi(SalesChannelContext $salesChannelContext) : PredbasketApi;

    /**
     * Creates the records api to update data directly in ff.
     *
     * @param string $salesChannelId
     * @return RecordsApi
     */
    public function createRecordsApi(string $salesChannelId): RecordsApi;

    /**
     * Creates the search api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return SearchApi
     */
    public function createSearchApi(SalesChannelContext $salesChannelContext): SearchApi;

    /**
     * Creates the tracking api instance, configured for the given sales channel.
     *
     * @param string $salesChannelId
     * @return TrackingApi
     */
    public function createTrackingApi(string $salesChannelId) : TrackingApi;
}
