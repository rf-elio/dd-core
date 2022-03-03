<?php

namespace Elio\FactFinder\Core\AdvisorCampaign\SalesChannel;

use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractAdvisorCampaignRoute
 * @package Elio\FactFinder\Core\AdvisorCampaign\SalesChannel
 * @author Ralf Frommherz
 */
abstract class AbstractAdvisorCampaignRoute
{
    abstract public function getDecorated(): AbstractAdvisorCampaignRoute;

    abstract public function load(Request $request, SalesChannelContext $context): ProductSearchRouteResponse;
}