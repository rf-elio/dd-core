<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Detail;

use Elio\ElioDataDiscovery\Api\Recommendations\RecommendationApi;
use Elio\ElioDataDiscovery\Api\Recommendations\Request\DetailPageRequest;
use Elio\ElioDataDiscovery\Api\Search\Response\CampaignFeedbackResponseCollection;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class ElioDataDiscoveryProductDetailRoute
 *
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @package Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Detail
 */
class ElioDataDiscoveryProductDetailRoute extends AbstractProductDetailRoute
{
    use ElioDataDiscoveryLogTrait;

    /**
     * ElioDataDiscoveryProductDetailRoute constructor.
     *
     * @param AbstractProductDetailRoute $decorated
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     * @param RecommendationApi $recordsApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AbstractProductDetailRoute       $decorated,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService,
        private readonly RecommendationApi                $recordsApi,
        LoggerInterface                                   $logger
    )
    {
        $this->logger = $logger;
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        return $this->decorated;
    }

    /**
     * Sends the detail page request to be able to show campaigns or feedback texts
     *
     * @param string $productId
     * @param Request $request
     * @param SalesChannelContext $context
     * @param Criteria $criteria
     * @return ProductDetailRouteResponse
     */
    public function load(
        string              $productId,
        Request             $request,
        SalesChannelContext $context,
        Criteria            $criteria
    ): ProductDetailRouteResponse
    {
        $config = $this->configService->getByContext($context);
        $productDetailResponse = $this->decorated->load($productId, $request, $context, $criteria);

        if (!$config->isActive() || !$config->isProductDetailPageCampaignsActive()) {
            return $productDetailResponse;
        }

        $detailPageRequest = (new DetailPageRequest(''))
            ->setId($productDetailResponse->getProduct()->getProductNumber())
            ->setWithSimilarProducts('false')
            ->setWithRecommendations('false')
            ->setWithRecord('false');

        try {
            $responseCollection = $this->recordsApi->getDetailPage($detailPageRequest, $context);
            if ($responseCollection->has(CampaignFeedbackResponseCollection::KEY)) {
                $productDetailResponse->getProduct()->addExtension(
                    CampaignFeedbackResponseCollection::KEY,
                    $responseCollection->get(CampaignFeedbackResponseCollection::KEY)
                );
            }

            return $productDetailResponse;
        } catch (Throwable $e) {
            $this->searchError($e->getMessage(), $this, [$context, $detailPageRequest]);
            return $productDetailResponse;
        }
    }
}