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

namespace Elio\ElioDataDiscovery\Api\Search;


use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Request\ContentSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\NavigationRequestProduct;
use Elio\ElioDataDiscovery\Api\Search\Request\ProductSearchRequest;
use Elio\ElioDataDiscovery\Api\Transform\Transformer;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class SearchApi
 * @package Elio\ElioDataDiscovery\Api\Search
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SearchApi
{
    use ElioDataDiscoveryLogTrait;

    /**
     * SearchApi constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ){
        $this->logger = $logger;
    }

    /**
     * Executes the elio search request
     *
     * @param ProductSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws Throwable
     */
    public function search(ProductSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $this->searchDebug('search', $this, [$searchRequest, $context]);
        return new ResponseCollection();
    }

    /**
     * @param ContentSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws Throwable
     */
    public function searchContent(ContentSearchRequest $searchRequest, SalesChannelContext $context) : ResponseCollection
    {
        $this->searchDebug('searchContent', $this, [$searchRequest, $context]);
        return new ResponseCollection();
    }

    /**
     * Executes the elio search navigation request
     *
     * @param NavigationRequestProduct $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws Throwable
     */
    public function navigation(NavigationRequestProduct $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $this->searchDebug('navigation', $this, [$searchRequest, $context]);
        return new ResponseCollection();
    }
}
