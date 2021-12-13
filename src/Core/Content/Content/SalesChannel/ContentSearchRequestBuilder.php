<?php

namespace Elio\FactFinder\Core\Content\Content\SalesChannel;


use Elio\FactFinder\Api\Search\Request\ContentSearchRequest;
use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Elio\FactFinder\Configuration\Configuration;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentSearchRequestBuilder
 * @package Elio\FactFinder\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentSearchRequestBuilder
{
    private FactFinderConfigServiceInterface $configService;

    /**
     * SearchRequestBuilder constructor.
     * @param FactFinderConfigServiceInterface $configService
     */
    public function __construct(FactFinderConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Builds the ff search request
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @param ContentSearchRequest|null $searchRequest
     * @return ContentSearchRequest
     */
    public function build(
        Request               $request,
        SalesChannelContext   $salesChannelContext,
        ?ContentSearchRequest $searchRequest = null
    ) : ContentSearchRequest
    {
        $config = $this->configService->getByContext($salesChannelContext);
        $searchRequest = $searchRequest ?? new ContentSearchRequest(
            $config->getApiContentChannel()
        );

        if(!empty($request->get('search'))) {
            $searchRequest->setQuery($request->get('search'));
        }
        $this->addCustomParameters($searchRequest, $config);

        return $searchRequest;
    }

    /**
     * Adds the additional request params to the ff request
     *
     * @param SearchRequest $searchRequest
     * @param Configuration $config
     */
    protected function addCustomParameters(SearchRequest $searchRequest, Configuration $config) : void
    {
        $searchRequest->setAdditionalRequestParameters($config->getAdditionalRequestParameters());
    }
}
