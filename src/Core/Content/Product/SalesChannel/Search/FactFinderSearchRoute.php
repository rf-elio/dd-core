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

namespace Elio\FactFinder\Core\Content\Product\SalesChannel\Search;

use Elio\FactFinder\Api\Search\Response\CampaignRedirectionResponse;
use Elio\FactFinder\Api\Search\Response\ContentListingResponse;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Search\SearchApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Content\Content\SalesChannel\ContentSearchRequestBuilder;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class FactFinderSearchRoute
 * @package Elio\FactFinder\Search
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FactFinderSearchRoute extends AbstractProductSearchRoute
{
    private AbstractProductSearchRoute $decorated;
    private ProductSearchRequestBuilder $productSearchRequestBuilder;
    private ContentSearchRequestBuilder $contentSearchRequestBuilder;
    private FactFinderConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private ProductListingResultTransformer $productListingResultTransformer;
    private LoggerInterface $logger;

    /**
     * FactFinderSearchRoute constructor.
     * @param AbstractProductSearchRoute $decorated
     * @param ProductSearchRequestBuilder $productSearchRequestBuilder
     * @param ContentSearchRequestBuilder $contentSearchRequestBuilder
     * @param FactFinderConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductListingResultTransformer $productListingResultTransformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractProductSearchRoute       $decorated,
        ProductSearchRequestBuilder      $productSearchRequestBuilder,
        ContentSearchRequestBuilder      $contentSearchRequestBuilder,
        FactFinderConfigServiceInterface $configService,
        SearchApi                        $searchApi,
        ProductListingResultTransformer  $productListingResultTransformer,
        LoggerInterface                  $logger
    )
    {
        $this->decorated = $decorated;
        $this->productSearchRequestBuilder = $productSearchRequestBuilder;
        $this->contentSearchRequestBuilder = $contentSearchRequestBuilder;
        $this->configService = $configService;
        $this->searchApi = $searchApi;
        $this->productListingResultTransformer = $productListingResultTransformer;
        $this->logger = $logger;
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    /**
     * Replaces the shopware search by ff search
     *
     * @throws Throwable
     */
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        $config = $this->configService->getByContext($context);
        if(!$config->isActive() || !$config->isSearchUseFactFinder()) {
            return $this->getDecorated()->load($request, $context, $criteria);
        }

        try {
            $searchRequest = $this->productSearchRequestBuilder->build($request, $criteria, $context);
            $resultCollection = $this->searchApi->search($searchRequest, $context);
            /** @var ProductListingResponse|null $productListingResponse */
            $productListingResponse = $resultCollection->get(ProductListingResponse::class);

            if (!$productListingResponse) {
                return $this->getDecorated()->load($request, $context, $criteria);
            }

            $shopwareProductListingResult = $this->productListingResultTransformer->transform(
                $productListingResponse, $criteria, $context
            );
            $shopwareProductListingResult->addCurrentFilter('search', $request->get('search'));

            /** @var CampaignRedirectionResponse|null $campaignRedirectionResponse */
            $campaignRedirectionResponse = $resultCollection->get(CampaignRedirectionResponse::class);
            $shopwareProductListingResult->addExtension(CampaignRedirectionResponse::class, $campaignRedirectionResponse);

            try {
                if ($config->isSearchUseContentChannel()) {
                    $contentSearchRequest = $this->contentSearchRequestBuilder->build($request, $context);
                    $contentSearchRequest->setQuery('*');
                    $resultCollection = $this->searchApi->searchContent($contentSearchRequest, $context);
                    /** @var ContentListingResponse|null $contentListingResponse */
                    $contentListingResponse = $resultCollection->get(ContentListingResponse::class);
                    $shopwareProductListingResult->addExtension(ContentListingResponse::class, $contentListingResponse);
                }
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage());
            }

            return new ProductSearchRouteResponse($shopwareProductListingResult);
        }
        catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return $this->getDecorated()->load($request, $context, $criteria);
        }
    }
}