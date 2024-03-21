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

namespace Elio\ElioDataDiscovery\Core\AdvisorCampaign\Subscriber;

use Elio\ElioDataDiscovery\Api\Search\Request\AdvisorStatus;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event\ProductListingResultTransformerEvent;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event\ProductSearchRequestBuildedEvent;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AdvisorSubscriber
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class AdvisorSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductSearchRequestBuildedEvent::class => 'onAdvisorCampaignRequest',
            ProductListingResultTransformerEvent::class => 'onAdvisorCampaign'
        ];
    }

    /**
     * @param ProductSearchRequestBuildedEvent $event
     * @return void
     */
    public function onAdvisorCampaignRequest(ProductSearchRequestBuildedEvent $event): void
    {
        $payload = $event->getPayload();
        $searchRequest = $event->getSearchRequest();

        $campaignId = null;
        $answerPath = null;

        foreach ($payload as $key => $value) {
            if (str_starts_with($key, ProductSearchRequestBuilder::ANSWER_PATH_REQUEST_PARAM_PREFIX)) {
                $campaignId = str_replace(ProductSearchRequestBuilder::ANSWER_PATH_REQUEST_PARAM_PREFIX, '', $key);
                $answerPath = $value;
            }
        }

        if (!empty($campaignId) && !empty($answerPath)) {
            $searchRequest->addExtension(AdvisorStatus::class, new AdvisorStatus($answerPath, $campaignId));
        }
    }

    /**
     * @param ProductListingResultTransformerEvent $event
     * @return void
     */
    public function onAdvisorCampaign(
        ProductListingResultTransformerEvent $event
    ): void
    {
        $shopwareProductListingResult = $event->getProductListingResult();
        $searchRequest = $event->getSearchRequest();
        $request = $event->getRequest();

        $listingMode = $request->get(ProductListingResultTransformer::LISTING_MODE_PARAMETER);

        if ($listingMode === ProductListingResultTransformer::LISTING_ADVISOR) {
            $showListing = false;
            $advisorStatus = $searchRequest->getExtension(AdvisorStatus::class);
            if ($advisorStatus instanceof AdvisorStatus && !empty($advisorStatus->getAnswerPath())) {
                $showListing = true;
            }

            $shopwareProductListingResult->addExtension('advisor-listing', new ArrayEntity([
                'showListing' => $showListing
            ]));
        }

        $event->setProductListingResult($shopwareProductListingResult);
    }
}