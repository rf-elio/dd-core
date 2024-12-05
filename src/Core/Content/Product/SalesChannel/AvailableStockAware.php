<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel;

use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

trait AvailableStockAware
{
    private function handleAvailableStock(
        Criteria $criteria,
        SalesChannelContext $context,
        SystemConfigService $systemConfigService,
        AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    ): void
    {
        $salesChannelId = $context->getSalesChannel()->getId();

        $hide = $systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId);

        if (!$hide) {
            return;
        }

        $closeoutFilter = $productCloseoutFilterFactory->create($context);
        $criteria->addFilter($closeoutFilter);
    }
}
