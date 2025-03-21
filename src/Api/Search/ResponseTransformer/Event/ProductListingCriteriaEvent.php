<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search\ResponseTransformer\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ProductListingCriteriaEvent extends Event implements ShopwareSalesChannelEvent
{
    public function __construct(
        private readonly array               $mainNumbers,
        private Criteria                     $criteria,
        private readonly SalesChannelContext $salesChannelContext
    ) {}

    public function getMainNumbers(): array
    {
        return $this->mainNumbers;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): void
    {
        $this->criteria = $criteria;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
