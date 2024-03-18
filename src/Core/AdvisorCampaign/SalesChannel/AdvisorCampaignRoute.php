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

namespace Elio\ElioSearch\Core\AdvisorCampaign\SalesChannel;

use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Api\Search\SearchApi;
use Elio\ElioSearch\Api\Search\Response\ProductListingResponse;
use Elio\ElioSearch\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\ElioSearch\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use OpenApi\Annotations as OA;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdvisorCampaignRoute
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
class AdvisorCampaignRoute extends AbstractAdvisorCampaignRoute
{
    /**
     * @param ElioSearchConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductSearchRequestBuilder $searchRequestBuilder
     * @param ProductListingResultTransformer $productListingResultTransformer
     */
    public function __construct(
        private readonly ElioSearchConfigServiceInterface $configService,
        private readonly SearchApi $searchApi,
        private readonly ProductSearchRequestBuilder $searchRequestBuilder,
        private readonly ProductListingResultTransformer  $productListingResultTransformer
    )
    {
    }

    /**
     * @return AbstractAdvisorCampaignRoute
     */
    public function getDecorated(): AbstractAdvisorCampaignRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *      path="/ff/campaign/advisor",
     *      summary="Fetch a list of products",
     *      description="List products that match the given criteria. For performance ressons a limit should always be set.",
     *      operationId="readProduct",
     *      tags={"Store API", "FF"},
     *      @OA\Parameter(name="Api-Basic-Parameters"),
     *      @OA\Response(
     *          response="200",
     *          description="Entity search result containing products",
     *          @OA\JsonContent(
     *              type="object",
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/EntitySearchResult"),
     *                  @OA\Schema(type="object",
     *                      @OA\Property(
     *                          type="array",
     *                          property="elements",
     *                          @OA\Items(ref="#/components/schemas/Product")
     *                      )
     *                  )
     *              }
     *          )
     *     )
     * )
     * @Route("/store-api/ff/campaign/advisor", name="store-api.e_ff.campaign.advisor", methods={"GET", "POST"})
     */
    public function load(Request $request, SalesChannelContext $context): ProductSearchRouteResponse
    {
        $config = $this->configService->getByContext($context);
        if (!$config->isActive()) {
            throw new BadRequestHttpException('Feature is not active');
        }

        $parameterName = $request->get('parameterName');
        $parameterValue = $request->get('parameterValue');

        if (empty($parameterName) || empty($parameterValue)) {
            throw new BadRequestHttpException('Parameter "parameterName" or "parameterValue" is empty or missing');
        }

        $criteria = new Criteria();
        $searchRequest = $this->searchRequestBuilder->build($request, $criteria, $context);
        $searchRequest->setAdditionalRequestParameters([$parameterName => $parameterValue]);
        $searchRequest->setQuery('*');

        $resultCollection = $this->searchApi->search($searchRequest, $context);

        /** @var ProductListingResponse $productListingResponse */
        $productListingResponse = $resultCollection->get(ProductListingResponse::class);

        $shopwareProductListingResult = $this->productListingResultTransformer->transform(
            $productListingResponse, $criteria, $context, $resultCollection, $searchRequest, $request
        );

        return new ProductSearchRouteResponse($shopwareProductListingResult);
    }
}