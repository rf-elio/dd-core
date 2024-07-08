<?php

namespace Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel;


use Elio\ElioDataDiscovery\Api\Request\ApiRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\ContentSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\SearchRequest;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentSearchRequestBuilder
 * @package Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentSearchRequestBuilder
{
    /**
     * SearchRequestBuilder constructor.
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     */
    public function __construct(
        private readonly ElioDataDiscoveryConfigServiceInterface $configService
    ) {}

    /**
     * Builds the elio search request
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
        $searchRequest ??= new ContentSearchRequest('');
        if(!empty($request->get('search'))) {
            $searchRequest->setQuery($request->get('search'));
        }
        $this->addCustomParameters($searchRequest, $config);
        $this->addMetaData($request, $searchRequest);

        return $searchRequest;
    }

    /**
     * Adds the additional request params to the elio search request
     *
     * @param SearchRequest $searchRequest
     * @param Configuration $config
     */
    protected function addCustomParameters(SearchRequest $searchRequest, Configuration $config) : void
    {
        $searchRequest->setAdditionalRequestParameters($config->getAdditionalRequestParameters());
    }

    protected function addMetaData(Request $request, ApiRequest $searchRequest): void
    {
        $searchRequest->setMetaDataFromRequest($request);
    }
}
