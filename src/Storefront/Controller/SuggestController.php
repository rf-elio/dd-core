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

namespace Elio\FactFinder\Storefront\Controller;

use Elio\FactFinder\Api\Search\Request\SuggestRequest;
use Elio\FactFinder\Api\Search\Response\SuggestionResponse;
use Elio\FactFinder\Api\Search\SuggestApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\SearchController;
use Shopware\Storefront\Page\Search\SearchPageLoader;
use Shopware\Storefront\Page\Suggest\SuggestPageLoader;
use Swagger\Client\ApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Class SuggestController
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class SuggestController extends SearchController
{
    private FactFinderConfigServiceInterface $configService;
    private SuggestApi $suggestApi;

    /**
     * @param FactFinderConfigServiceInterface $configService
     * @param SuggestApi $suggestApi
     * @param SearchPageLoader $searchPageLoader
     * @param SuggestPageLoader $suggestPageLoader
     * @param AbstractProductSearchRoute $productSearchRoute
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        SuggestApi $suggestApi,
        SearchPageLoader $searchPageLoader,
        SuggestPageLoader $suggestPageLoader,
        AbstractProductSearchRoute $productSearchRoute
    ) {
        parent::__construct(
            $searchPageLoader,
            $suggestPageLoader,
            $productSearchRoute
        );
        $this->configService = $configService;
        $this->suggestApi = $suggestApi;
    }

    /**
     * Replaces the shopware suggestions with ff suggestions in the case this feature is activated
     *
     * @param SalesChannelContext $context
     * @param Request $request
     * @return Response
     * @throws ApiException
     * @throws Throwable
     */
    public function suggest(SalesChannelContext $context, Request $request): Response
    {
        $config = $this->configService->getByContext($context);
        if (!$config->isActive() || !$config->isSuggestUseFactFinder()) {
            return parent::suggest($context, $request);
        }

        $suggestRequest = new SuggestRequest($config->getApiChannel());
        $searchTerm = $request->get('search') ?? '*';
        $suggestRequest->setQuery($searchTerm);
        $resultCollection = $this->suggestApi->suggest($suggestRequest, $context);

        /** @var SuggestionResponse|null $suggestionResponse */
        $suggestionResponse = $resultCollection->get(SuggestionResponse::class);

        if (!$suggestionResponse) {
            return parent::suggest($context, $request);
        }

        return $this->renderStorefront(
            '@Storefront/storefront/page/elio-suggest/search-suggest.html.twig',
            [
                'response' => $suggestionResponse,
                'searchTerm' => $searchTerm
            ]
        );
    }
}
