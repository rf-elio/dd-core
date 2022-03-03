<?php

namespace Elio\FactFinder\Core\AdvisorCampaign\SalesChannel;

use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Search\SearchApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use OpenApi\Annotations as OA;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class AdvisorCampaignRoute extends AbstractAdvisorCampaignRoute
{
    private FactFinderConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private ProductSearchRequestBuilder $searchRequestBuilder;
    private ProductListingResultTransformer $productListingResultTransformer;

    /**
     * @param FactFinderConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductSearchRequestBuilder $searchRequestBuilder
     * @param ProductListingResultTransformer $productListingResultTransformer
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        SearchApi $searchApi,
        ProductSearchRequestBuilder $searchRequestBuilder,
        ProductListingResultTransformer  $productListingResultTransformer
    )
    {
        $this->configService = $configService;
        $this->searchApi = $searchApi;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->productListingResultTransformer = $productListingResultTransformer;
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
            $productListingResponse, $criteria, $context, $resultCollection
        );

        return new ProductSearchRouteResponse($shopwareProductListingResult);
    }
}