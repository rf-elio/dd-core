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

namespace Elio\ElioDataDiscovery\Storefront\Controller;

use Elio\ElioDataDiscovery\Core\AdvisorCampaign\SalesChannel\AbstractAdvisorCampaignRoute;
use Elio\ElioDataDiscovery\Core\AdvisorCampaign\Subscriber\AdvisorSubscriber;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JsonException;

/**
 * Class AdvisorCampaignController
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class AdvisorCampaignController extends StorefrontController
{
    /**
     * @param AbstractAdvisorCampaignRoute $advisorCampaignRoute
     */
    public function __construct(
        private readonly AbstractAdvisorCampaignRoute $advisorCampaignRoute
    )
    {
    }

    /**
     * @Route("/widgets/edd/campaign/advisor", name="frontend.e-dd.campaign.advisor", methods={"POST", "GET"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     * @throws JsonException
     */
    #[Route('/widgets/edd/campaign/advisor', name: 'frontend.e-dd.campaign.advisor', defaults: ['csrf_protected' => false, 'XmlHttpRequest' => true], methods: ['POST', 'GET'])]
    public function campaign(Request $request, SalesChannelContext $context): Response
    {
        $this->injectParametersByRequestContent($request);
        $request->request->set(AdvisorSubscriber::LISTING_MODE_PARAMETER, AdvisorSubscriber::LISTING_ADVISOR);
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
                    ProductSearchRequestBuilder::ADDITIONAL_REQUEST_PARAM_PREFIX . $parameterName => $parameterValue,
                    AdvisorSubscriber::LISTING_MODE_PARAMETER => AdvisorSubscriber::LISTING_ADVISOR
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
