<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search;


use Elio\ElioDataDiscovery\Api\Exception\ApiException;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Request\ContentSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\NavigationRequestProduct;
use Elio\ElioDataDiscovery\Api\Search\Request\PriceRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\ProductSearchRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class SearchApi
 * @package Elio\FactFinder\Api\Search
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
interface SearchApiInterface
{
    /**
     * Executes the data discovery search request
     *
     * @param ProductSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function search(ProductSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection;

    /**
     * @param ContentSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function searchContent(ContentSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection;

    /**
     * @param PriceRequest $priceRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function searchPrices(PriceRequest $priceRequest, SalesChannelContext $context): ResponseCollection;

    /**
     * Executes the ff navigation request
     *
     * @param NavigationRequestProduct $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function navigation(NavigationRequestProduct $searchRequest, SalesChannelContext $context): ResponseCollection;
}
