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

namespace Elio\FactFinder\Core\Content\Product\SalesChannel;

use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Elio\FactFinder\Api\Search\Response\AdvisorCampaignResponseCollection;
use Elio\FactFinder\Api\Search\Response\CampaignFeedbackResponseCollection;
use Elio\FactFinder\Api\Search\Response\CampaignRedirectionResponse;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductListingResultTransformer
 * @package Elio\FactFinder\Core\Content\Product\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ProductListingResultTransformer
{
    public const FF_LISTING_MODE_PARAMETER = 'ff_listing_mode';
    public const FF_LISTING_ADVISOR = 'advisor';

    /**
     * Transforms the ProductListingResponse to an shopware ProductListingResult
     *
     * @param ProductListingResponse $productListingResponse
     * @param Criteria $criteria
     * @param SalesChannelContext $context
     * @param ResponseCollection $resultCollection
     * @param SearchRequest $searchRequest
     * @param Request $request
     * @return ProductListingResult
     */
    public function transform(
        ProductListingResponse $productListingResponse,
        Criteria $criteria,
        SalesChannelContext $context,
        ResponseCollection $resultCollection,
        SearchRequest $searchRequest,
        Request $request
    ) : ProductListingResult
    {
        $shopwareProductListingResult = new ProductListingResult(
            ProductDefinition::ENTITY_NAME,
            $productListingResponse->getTotalHits(),
            $productListingResponse->getProducts(),
            $productListingResponse->getAggregations(),
            $criteria,
            $context->getContext()
        );

        $this->addSorting($productListingResponse, $shopwareProductListingResult);
        $this->addPagination($productListingResponse, $shopwareProductListingResult, $criteria);
        $this->addCampaigns($resultCollection, $shopwareProductListingResult);
        $this->handleListingMode($shopwareProductListingResult, $searchRequest, $request);
        return $shopwareProductListingResult;
    }


    /**
     * Adds the applied sorting to the shopware result
     *
     * @param ProductListingResponse $productListingResponse
     * @param ProductListingResult $shopwareProductListingResult
     */
    protected function addSorting(
        ProductListingResponse $productListingResponse,
        ProductListingResult $shopwareProductListingResult
    ) : void
    {
        $shopwareProductListingResult->setAvailableSortings($productListingResponse->getAvailableSortings());
        if($productListingResponse->getCurrentSorting()) {
            $shopwareProductListingResult->setSorting($productListingResponse->getCurrentSorting()->getKey());
        }
    }

    /**
     * Adds the pagination to the shopware result
     *
     * @param ProductListingResponse $productListingResponse
     * @param ProductListingResult $shopwareProductListingResult
     * @param Criteria $criteria
     */
    protected function addPagination(
        ProductListingResponse $productListingResponse,
        ProductListingResult $shopwareProductListingResult,
        Criteria $criteria
    ) : void
    {
        $limit = $productListingResponse->getHitsPerPage();
        $page = $productListingResponse->getCurrentPage();

        $shopwareProductListingResult->setLimit($limit);
        $shopwareProductListingResult->setPage($page);

        $criteria->setLimit($limit);
        $criteria->setOffset($limit * ($page - 1));
    }

    /**
     * Adds the present campaigns to the search result
     *
     * @param ResponseCollection $resultCollection
     * @param EntitySearchResult $shopwareProductListingResult
     */
    protected function addCampaigns(ResponseCollection $resultCollection, EntitySearchResult $shopwareProductListingResult) : void
    {
        /** @var CampaignRedirectionResponse|null $campaignRedirectionResponse */
        $campaignRedirectionResponse = $resultCollection->get(CampaignRedirectionResponse::class);
        $shopwareProductListingResult->addExtension(CampaignRedirectionResponse::class, $campaignRedirectionResponse);

        /** @var CampaignFeedbackResponseCollection|null $campaignFeedbackResponseCollection */
        $campaignFeedbackResponseCollection = $resultCollection->get(CampaignFeedbackResponseCollection::KEY);
        $shopwareProductListingResult->addExtension(CampaignFeedbackResponseCollection::KEY, $campaignFeedbackResponseCollection);

        $advisorCampaignResponse = $resultCollection->get(AdvisorCampaignResponseCollection::KEY);
        $shopwareProductListingResult->addExtension(AdvisorCampaignResponseCollection::KEY, $advisorCampaignResponse);
    }

    private function handleListingMode(
        ProductListingResult $shopwareProductListingResult,
        SearchRequest $searchRequest,
        Request $request
    ): void
    {
        $listingMode = $request->get(self::FF_LISTING_MODE_PARAMETER);

        if ($listingMode === self::FF_LISTING_ADVISOR) {
            $showListing = false;
            if ($searchRequest->getAdvisorStatus() && !empty($searchRequest->getAdvisorStatus()->getAnswerPath())) {
                $showListing = true;
            }

            $shopwareProductListingResult->addExtension('ff-advisor-listing', new ArrayEntity([
                'showListing' => $showListing
            ]));
        }
    }
}
