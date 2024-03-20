<?php


namespace Elio\ElioDataDiscovery\Tests\Core\Export\Mock\Repository;


use Elio\ElioDataDiscovery\Tests\Core\Export\Mock\EntityDefinitionMock;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;
use Shopware\Core\Framework\Event\NestedEventCollection;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class ProductRepositoryMock
 *
 * @package Elio\ElioDataDiscovery\Tests\Core\Export\Mock\Repository
 */
class ProductRepositoryMock extends EntityRepository
{
    use KernelTestBehaviour;

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
        if ($criteria->getOffset() > 0) {
            return new EntitySearchResult(
                $this->getDefinition()->getEntityName(),
                0,
                new ProductCollection([]),
                null,
                $criteria,
                $context
            );
        }

        return new EntitySearchResult(
            $this->getDefinition()->getEntityName(),
            2,
            new ProductCollection([
                $this->createProduct('product1', 'productNumber1'),
                $this->createProduct('product2', 'productNumber2')
            ]),
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

    private function createProduct($name, $productNumber): ProductEntity
    {
        $product = new ProductEntity();
        $product->setId(Uuid::fromStringToHex($name));
        $product->setName($name);
        $product->setProductNumber($productNumber);
        $product->setManufacturerNumber('test');
        $product->setDescription('test');
        $product->setStock(1);
        $product->setSales(1);

        $priceCollection = new PriceCollection();
        $priceCollection->add(new Price(Defaults::CURRENCY, 150, 200, false));
        $priceCollection->add(new Price(Defaults::CURRENCY, 150, 200, false));
        $product->setPrice($priceCollection);

        $manufacturer = new ProductManufacturerEntity();
        $manufacturer->setName('test');
        $product->setManufacturer($manufacturer);

        $category = new CategoryEntity();
        $category->setId(Uuid::randomHex());
        $category->setPath('1|2|3|');
        $category->addTranslated('breadcrumb', ['1' => 'test 1', '2' => 'test 2', '3' => 'breadcrumb 3', $category->getId() => 'breadcrumb 4']);
        $categoryCollection = new CategoryCollection();
        $categoryCollection->add($category);
        $product->setCategories($categoryCollection);


        return $product;
    }
}
