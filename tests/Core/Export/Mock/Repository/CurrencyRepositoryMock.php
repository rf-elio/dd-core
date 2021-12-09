<?php


namespace Elio\FactFinder\Tests\Core\Export\Mock\Repository;


use Elio\FactFinder\Tests\Core\Export\Mock\EntityDefinitionMock;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;
use Shopware\Core\System\Currency\CurrencyCollection;
use Shopware\Core\System\Currency\CurrencyEntity;

/**
 * Class CurrencyRepositoryMock
 *
 * @package Elio\FactFinder\Tests\Core\Export\Mock\Repository
 */
class CurrencyRepositoryMock implements EntityRepositoryInterface
{
    public function getDefinition(): EntityDefinition
    {
        return new EntityDefinitionMock();
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        // TODO: Implement aggregate() method.
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        // TODO: Implement searchIds() method.
    }

    public function clone(
        string $id,
        Context $context,
        ?string $newId = null,
        ?CloneBehavior $behavior = null
    ): EntityWrittenContainerEvent {
        // TODO: Implement clone() method.
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $currency = new CurrencyEntity();
        $currency->setId(Defaults::CURRENCY);
        $currency->setIsoCode('USD');
        $currency->setSymbol('$');

        return new EntitySearchResult(
            $this->getDefinition()->getEntityName(),
            1,
            new CurrencyCollection([$currency]),
            null,
            $criteria,
            $context
        );
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        // TODO: Implement update() method.
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        // TODO: Implement upsert() method.
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        // TODO: Implement create() method.
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        // TODO: Implement delete() method.
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        // TODO: Implement createVersion() method.
    }

    public function merge(string $versionId, Context $context): void
    {
        // TODO: Implement merge() method.
    }

}
