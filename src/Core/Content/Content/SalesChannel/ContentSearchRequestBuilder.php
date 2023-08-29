<?php

namespace Elio\ElioSearch\Core\Content\Content\SalesChannel;


use Elio\ElioSearch\Api\Search\Request\ContentSearchRequest;
use Elio\ElioSearch\Api\Search\Request\SearchRequest;
use Elio\ElioSearch\Configuration\Configuration;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentSearchRequestBuilder
 * @package Elio\ElioSearch\Core\Content\Content\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentSearchRequestBuilder
{
    private ElioSearchConfigServiceInterface $configService;

    /**
     * SearchRequestBuilder constructor.
     * @param ElioSearchConfigServiceInterface $configService
     */
    public function __construct(ElioSearchConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

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
     * Adds the additional request params to the elio search request
     *
     * @param SearchRequest $searchRequest
     * @param Configuration $config
     */
    protected function addCustomParameters(SearchRequest $searchRequest, Configuration $config) : void
    {
        $searchRequest->setAdditionalRequestParameters($config->getAdditionalRequestParameters());
    }
}
