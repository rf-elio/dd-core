<?php declare(strict_types=1);

/**
 * Copyright (c) 2020, elio GmbH.
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

use Elio\FactFinder\Components\ElioFactFinderService;
use Elio\FactFinder\Components\Helper\FactFinderHelper;
use Elio\FactFinder\Service\FactFinderConfiguration;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Response;


/**
 *
 * Class AfterSearchNavigationController
 *
 * @category  Controller
 * @package   Shopware\Plugins\FactFinder\Storefront\Controller
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 *
 * @RouteScope(scopes={"storefront"})
 */
class AfterSearchNavigationController extends StorefrontController
{
    /**
     * @var ElioFactFinderService
     */
    private $ffService;

    /**
     * @var FactFinderHelper
     */
    private $ffHelper;

    public function __construct(ElioFactFinderService $ffService, FactFinderHelper $ffHelper)
    {
        $this->ffService = $ffService;
        $this->ffHelper = $ffHelper;
    }

    /**
     * @Route("filter", name="frontend.filter", options={"seo"="false"}, methods={"GET"})
     * @throws MissingRequestParameterException
     */
    public function filter(Request $request, SalesChannelContext $context): Response
    {
        /*
        if (!$request->query->has('searchParams')) {
            throw new MissingRequestParameterException('searchParams');
        }
        */
        $searchParams = '/Eliomedia7.2/Search.ff?query=industrial&filterCategoryPathROOT=Automotive%2C+Industrial+%26amp%3B+Toys&channel=sw610&productsPerPage=24&followSearch=9993&format=JSON';
        $ffSearchResult = $this->ffService->search(null, $searchParams);
        $filteredProducts = $this->ffHelper->convertRecords($context, new Criteria(), $ffSearchResult['records']);


        //dd($filteredProducts);
        return $this->renderStorefront('@Storefront/storefront/component/product/listing.html.twig', ['$filteredProducts' => $filteredProducts]);
    }

}
