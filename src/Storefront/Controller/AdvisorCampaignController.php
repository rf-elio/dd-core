<?php

namespace Elio\ElioSearch\Storefront\Controller;

use Elio\ElioSearch\Core\AdvisorCampaign\SalesChannel\AbstractAdvisorCampaignRoute;
use Elio\ElioSearch\Core\Content\Product\SalesChannel\ProductListingResultTransformer;
use Elio\ElioSearch\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use JsonException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class AdvisorCampaignController extends StorefrontController
{
    private AbstractAdvisorCampaignRoute $advisorCampaignRoute;

    /**
     * @param AbstractAdvisorCampaignRoute $advisorCampaignRoute
     */
    public function __construct(AbstractAdvisorCampaignRoute $advisorCampaignRoute) {
        $this->advisorCampaignRoute = $advisorCampaignRoute;
    }

    /**
     * @Route("/widgets/elio-search/campaign/advisor", name="frontend.e-elio-search.campaign.advisor", methods={"POST", "GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     * @throws JsonException
     */
    public function campaign(Request $request, SalesChannelContext $context): Response
    {
        $this->injectParametersByRequestContent($request);
        $request->request->set(ProductListingResultTransformer::ELIO_SEARCH_LISTING_MODE_PARAMETER, ProductListingResultTransformer::ELIO_SEARCH_LISTING_ADVISOR);
        $result = $this->advisorCampaignRoute->load($request, $context);

        $result->getListingResult()->getCriteria()->setLimit(-1);
        $result->getListingResult()->clear();

        $parameterName = $request->request->get('parameterName', '');
        $parameterValue = $request->request->get('parameterValue', '');

        return $this->renderStorefront(
            '@Storefront/storefront/page/elio-advisor-campaign/index.html.twig',
            [
                'productListing' => $result->getListingResult(),
                'searchParams' => [
                    'search' => '*',
                    ProductSearchRequestBuilder::ADDITIONAL_REQUEST_PARAM_PREFIX.$parameterName => $parameterValue,
                    ProductListingResultTransformer::ELIO_SEARCH_LISTING_MODE_PARAMETER => ProductListingResultTransformer::ELIO_SEARCH_LISTING_ADVISOR
                ]
            ]
        );
    }

    /**
     * Injects the parameters required by the core api by the request content
     *
     * @param Request $request
     * @throws JsonException
     */
    private function injectParametersByRequestContent(Request $request): void
    {
        if (empty($request->getContent())) {
            return;
        }

        $params = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($params['parameterName']) && is_string($params['parameterName'])) {
            $request->request->set('parameterName', $params['parameterName']);
        }

        if (isset($params['parameterValue']) && is_string($params['parameterValue'])) {
            $request->request->set('parameterValue', $params['parameterValue']);
        }
    }
}
