<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\AdvisorCampaign\SalesChannel;

use Elio\ElioSearch\Api\Search\Response\ProductListingResponse;
use Elio\ElioSearch\Api\Search\SearchApi;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
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
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
class AdvisorCampaignRoute extends AbstractAdvisorCampaignRoute
{
    private ElioSearchConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private ProductSearchRequestBuilder $searchRequestBuilder;
    private ProductListingResultTransformer $productListingResultTransformer;

    /**
     * @param ElioSearchConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductSearchRequestBuilder $searchRequestBuilder
     * @param ProductListingResultTransformer $productListingResultTransformer
     */
    public function __construct(
        ElioSearchConfigServiceInterface $configService,
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
     *      path="/elio-search/campaign/advisor",
     *      summary="Fetch a list of products",
     *      description="List products that match the given criteria. For performance ressons a limit should always be set.",
     *      operationId="readProduct",
     *      tags={"Store API", "ElioSearch"},
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
     * @Route("/store-api/elio-search/campaign/advisor", name="store-api.e_elio-search.campaign.advisor", methods={"GET", "POST"})
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