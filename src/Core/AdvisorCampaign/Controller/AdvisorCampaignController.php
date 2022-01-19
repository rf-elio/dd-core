<?php

namespace Elio\FactFinder\Core\AdvisorCampaign\Controller;

use Elio\FactFinder\Api\Search\Request\AdvisorStatus;
use Elio\FactFinder\Api\Search\Response\AdvisorCampaignResponseCollection;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Search\SearchApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AdvisorCampaignController extends StorefrontController
{
    private FactFinderConfigServiceInterface $configService;
    private SearchApi $searchApi;
    private ProductSearchRequestBuilder $searchRequestBuilder;
    private LoggerInterface $logger;

    /**
     * @param FactFinderConfigServiceInterface $configService
     * @param SearchApi $searchApi
     * @param ProductSearchRequestBuilder $searchRequestBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        SearchApi $searchApi,
        ProductSearchRequestBuilder $searchRequestBuilder,
        LoggerInterface $logger
    ) {
        $this->configService = $configService;
        $this->searchApi = $searchApi;
        $this->searchRequestBuilder = $searchRequestBuilder;
        $this->logger = $logger;
    }

    /**
     * @Route("/elio-ff-advisor-campaign", name="frontend.elio-ff.advisor-campaign", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function campaign(Request $request, SalesChannelContext $context): JsonResponse
    {
        try {
            $config = $this->configService->getByContext($context);
            if (!$config->isActive()) {
                throw new RuntimeException('FactFinder is not active');
            }

            $criteria = new Criteria();
            $searchRequest = $this->searchRequestBuilder->build($request, $criteria, $context);
            $searchRequest->setQuery($config->getSearchTermForAdvisorCmsElement());
            $resultCollection = $this->searchApi->search($searchRequest, $context);
            /** @var AdvisorCampaignResponseCollection $advisorCampaignResponseCollection */
            $advisorCampaignResponseCollection = $resultCollection->get(AdvisorCampaignResponseCollection::KEY);

            $campaign = !empty($request->get('campaignName')) ?
                        $advisorCampaignResponseCollection->getByName($request->get('campaignName')) :
                        $advisorCampaignResponseCollection->getFirstCampaign();

            return $this->json([
                'success' => true,
                'data' => $campaign !== null ? [
                    'id' => $campaign->getId(),
                    'questions' => $campaign->questionsToArray()
                ] : null
            ]);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/elio-ff-advisor-products", name="frontend.elio-ff.advisor-products", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function products(Request $request, SalesChannelContext $context): JsonResponse
    {
        try {
            $config = $this->configService->getByContext($context);
            if (!$config->isActive()) {
                throw new RuntimeException('FactFinder is not active');
            }

            $criteria = new Criteria();
            $searchRequest = $this->searchRequestBuilder->build($request, $criteria, $context);
            if (($advisorStatus = $request->get('advisorStatus')) !== null) {
                $searchRequest->setAdvisorStatus(new AdvisorStatus($advisorStatus['answerPath'], $advisorStatus['id']));
                $searchRequest->setHitsPerPage($config->getMaxAdvisorProducts());
                $resultCollection = $this->searchApi->search($searchRequest, $context);
                /** @var ProductListingResponse $productListingResponse */
                $productListingResponse = $resultCollection->get(ProductListingResponse::class);
                $products = $productListingResponse->getProducts();
            } else {
                $products = new ProductCollection();
            }

            return $this->json([
                'success' => true,
                'data' => $this->renderStorefront('storefront/component/factfinder/campaign/products.html.twig', [
                    'products' => $products
                ])->getContent(),
                'productsCount' => $products->count()
            ]);

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
