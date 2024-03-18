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

namespace Elio\ElioDataDiscovery\Api\Recommendations;

use Elio\ElioDataDiscovery\Api\Recommendations\Request\DetailPageRequest;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\RecommendationRequest;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\SimilarRequest;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class RecordsApi
 *
 * @package Elio\ElioDataDiscovery\Api\Recommendations
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class RecommendationApi
{
    use ElioDataDiscoveryLogTrait;

    /**
     * RecommendationApi constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ){
        $this->logger = $logger;
    }

    /**
     * @param DetailPageRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws Throwable
     */
    public function getDetailPage(DetailPageRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $this->searchDebug('RecommendationApi::getDetailPage', $this, [$request, $context]);
        return new ResponseCollection();
    }

    /**
     * @param RecommendationRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws Throwable
     */
    public function getRecommendations(RecommendationRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $this->searchDebug('RecommendationApi::getRecommendations', $this, [$request, $context]);
        return new ResponseCollection();
    }

    /**
     * @param SimilarRequest $request
     * @param SalesChannelContext $context
     *
     * @return ResponseCollection
     * @throws Throwable
     */
    public function getSimilar(SimilarRequest $request, SalesChannelContext $context): ResponseCollection
    {
        $this->searchDebug('RecommendationApi::getSimilar', $this, [$request, $context]);
        return new ResponseCollection();
    }
}
