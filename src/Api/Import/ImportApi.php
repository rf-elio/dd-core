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

namespace Elio\FactFinder\Api\Import;

use Elio\FactFinder\Api\ApiClientFactoryInterface;
use Elio\FactFinder\Api\Import\Request\SearchImportRequest;
use Elio\FactFinder\Api\Import\Request\SuggestImportRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Transform\Transformer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Throwable;

/**
 * Class ImportApi
 * @package Elio\FactFinder\Api\Import
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ImportApi
{
    private ApiClientFactoryInterface $apiFactory;
    private Transformer $transformer;

    /**
     * ImportApi constructor.
     * @param ApiClientFactoryInterface $apiFactory
     * @param Transformer $transformer
     */
    public function __construct(ApiClientFactoryInterface $apiFactory, Transformer $transformer)
    {
        $this->apiFactory = $apiFactory;
        $this->transformer = $transformer;
    }

    /**
     * Executes the ff search import request
     * @param SearchImportRequest $importRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function searchImport(SearchImportRequest $importRequest, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createImportApi($context);
        $results = $apiClient->startSearchImportUsingPOST(
            [$importRequest->getChannel()],
            $importRequest->isDownload(),
            $importRequest->isCacheFlush(),
            $importRequest->isQuiet(),
            $importRequest->getImportStage(),
            $importRequest->isIncludeCustomerPrices()
        );

        $result = array_shift($results);
        return $this->transformer->transformResponse($result, $context, $importRequest);
    }

    /**
     * Executes the ff suggest import request
     * @param SuggestImportRequest $importRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function suggestImport(SuggestImportRequest $importRequest, SalesChannelContext $context): ResponseCollection
    {
        $apiClient = $this->apiFactory->createImportApi($context);
        $results = $apiClient->startSuggestImportUsingPOST(
            [$importRequest->getChannel()],
            $importRequest->isQuiet()
        );

        $result = array_shift($results);
        return $this->transformer->transformResponse($result, $context, $importRequest);
    }
}