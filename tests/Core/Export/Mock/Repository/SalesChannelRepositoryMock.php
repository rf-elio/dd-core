<?php


namespace Elio\ElioDataDiscovery\Tests\Core\Export\Mock\Repository;


use Elio\ElioDataDiscovery\Tests\Core\Export\Mock\EntityDefinitionMock;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;
use Shopware\Core\Framework\Event\NestedEventCollection;
use Shopware\Core\System\Currency\CurrencyCollection;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class SalesChannelRepositoryMock
 *
 * @package Elio\ElioDataDiscovery\Tests\Core\Export\Mock\Repository
 */
class SalesChannelRepositoryMock extends EntityRepository
{
    public function getDefinition(): EntityDefinition
    {
        return new EntityDefinitionMock();
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        return new AggregationResultCollection();
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        return new IdSearchResult(0, [], $criteria, $context);
    }

    public function clone(
        string $id,
        Context $context,
        ?string $newId = null,
        ?CloneBehavior $behavior = null
    ): EntityWrittenContainerEvent {
        return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $currency = new CurrencyEntity();
        $currency->setId(Defaults::CURRENCY);

        $salesChannel = new SalesChannelEntity();
        $salesChannel->setId(Defaults::SALES_CHANNEL_TYPE_STOREFRONT);
        $salesChannel->setCurrencies(new CurrencyCollection([$currency]));

        return new EntitySearchResult(
            $this->getDefinition()->getEntityName(),
            1,
            new SalesChannelCollection([$salesChannel]),
            null,
            $criteria,
            $context
        );
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return '';
    }

    public function merge(string $versionId, Context $context): void
    {
    }
}
