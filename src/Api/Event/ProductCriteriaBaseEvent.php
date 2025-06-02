<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ProductCriteriaBaseEvent extends Event implements ShopwareSalesChannelEvent
{
    public function __construct(
        private readonly array               $productNumbers,
        private Criteria                     $criteria,
        private readonly SalesChannelContext $salesChannelContext
    ) {}

    public function getProductNumbers(): array
    {
        return $this->productNumbers;
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
