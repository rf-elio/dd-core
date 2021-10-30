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

namespace Elio\FactFinder\Core\Content\Product\SalesChannel\Listing;


use Elio\FactFinder\Api\Search\Request\NavigationRequest;
use Elio\FactFinder\Api\Search\Response\CampaignRedirectionResponse;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Search\SearchApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\FactFinder\Core\Content\Product\SalesChannel\SearchRequestBuilder;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class FactFinderProductListingRoute
 * @package Elio\FactFinder\Core\Content\Product\SalesChannel\Listing
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FactFinderProductListingRoute extends AbstractProductListingRoute
{
    private AbstractProductListingRoute $decorated;
    private FactFinderConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private SearchRequestBuilder $searchRequestBuilder;
    private ProductListingResultTransformer $productListingResultTransformer;
    private EntityRepositoryInterface $categoryRepository;
    private CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder;

    /**
     * FactFinderProductListingRoute constructor.
     * @param AbstractProductListingRoute $decorated
     * @param SearchRequestBuilder $searchRequestBuilder
     * @param FactFinderConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductListingResultTransformer $productListingResultTransformer
     * @param EntityRepositoryInterface $categoryRepository
     * @param CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder
     */
    public function __construct(
        AbstractProductListingRoute $decorated,
        SearchRequestBuilder $searchRequestBuilder,
        FactFinderConfigServiceInterface $configService,
        SearchApi $searchApi,
        ProductListingResultTransformer $productListingResultTransformer,
        EntityRepositoryInterface $categoryRepository,
        CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder
    )
    {
        $this->decorated = $decorated;
        $this->configService = $configService;
        $this->searchApi = $searchApi;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->productListingResultTransformer = $productListingResultTransformer;
        $this->categoryRepository = $categoryRepository;
        $this->categoryBreadcrumbBuilder = $categoryBreadcrumbBuilder;
    }

    /**
     * @return AbstractProductListingRoute
     */
    public function getDecorated(): AbstractProductListingRoute
    {
        return $this->decorated;
    }

    /**
     * Replaces the shopware product listing result by the ff product listing result
     *
     * @param string $categoryId
     * @param Request $request
     * @param SalesChannelContext $context
     * @param Criteria $criteria
     * @return ProductListingRouteResponse
     * @throws ApiException
     * @throws Throwable
     */
    public function load(string $categoryId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $config = $this->configService->getByContext($context);
        if(!$config->isActive() || !$config->isListingUseFactFinder()) {
            return $this->decorated->load($categoryId, $request, $context, $criteria);
        }
        /** @var NavigationRequest $navigationRequest */
        $navigationRequest = $this->searchRequestBuilder->build(
            $request, $criteria, $context, new NavigationRequest($config->getApiChannel())
        );
        $this->addCurrentCategoryToNavigationRequest($navigationRequest, $categoryId, $context);

        $resultCollection = $this->searchApi->navigation($navigationRequest, $context);
        /** @var ProductListingResponse|null $productListingResponse */
        $productListingResponse = $resultCollection->get(ProductListingResponse::class);

        if(!$productListingResponse) {
            return $this->decorated->load($categoryId, $request, $context, $criteria);
        }

        $shopwareProductListingResult = $this->productListingResultTransformer->transform(
            $productListingResponse, $criteria, $context
        );
        $shopwareProductListingResult->addCurrentFilter('navigationId', $categoryId);

        /** @var CampaignRedirectionResponse|null $campaignRedirectionResponse */
        $campaignRedirectionResponse = $resultCollection->get(CampaignRedirectionResponse::class);
        $shopwareProductListingResult->addExtension(CampaignRedirectionResponse::class, $campaignRedirectionResponse);

        return new ProductListingRouteResponse($shopwareProductListingResult);
    }

    /**
     * Adds the path to the current category and the id to the ff api request to filter for that category.
     *
     * @param NavigationRequest $navigationRequest
     * @param string $categoryId
     * @param SalesChannelContext $context
     */
    protected function addCurrentCategoryToNavigationRequest(NavigationRequest $navigationRequest, string $categoryId, SalesChannelContext $context): void
    {
        $category = $this->categoryRepository->search(new Criteria([$categoryId]), $context->getContext())->getEntities()->first();
        $path = $this->categoryBreadcrumbBuilder->build($category, $context->getSalesChannel());
        $path = implode('/', array_values($path));
        $navigationRequest->setCategoryPath($path);
        $navigationRequest->setCategoryId($categoryId);
    }
}