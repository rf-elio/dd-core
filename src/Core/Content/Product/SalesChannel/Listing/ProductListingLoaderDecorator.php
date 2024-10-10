<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Listing;

use Doctrine\DBAL\Connection;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\DisableVariantGroupingInListingLoaderStruct;
use Shopware\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * This class decorates the ProductListingLoader. It works almost identical to Shopware's ProductListingLoader, but
 * since we need to load the products that are sent by BI, Shopware's ProductListingLoader overwrites our result with
 * the resolvePreviews method. The ProductListingLoaderDecorator removes this method as well as the addGroup method,
 * which is not needed anymore, since we have grouping settings in BI.
 */
class ProductListingLoaderDecorator extends ProductListingLoader
{
    public function __construct(
        private readonly ProductListingLoader $decorated,
        private readonly SalesChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    )
    {
        parent::__construct(
            $productRepository,
            $systemConfigService,
            $connection,
            $eventDispatcher,
            $productCloseoutFilterFactory
        );
    }

    public function load(Criteria $origin, SalesChannelContext $context): EntitySearchResult
    {
        if (!$origin->hasExtension(DisableVariantGroupingInListingLoaderStruct::class)) {
            $this->decorated->load($origin, $context);
        }

        $origin->addState(Criteria::STATE_ELASTICSEARCH_AWARE);
        $criteria = clone $origin;

        $this->handleAvailableStock($criteria, $context);

        $ids = $this->productRepository->searchIds($criteria, $context);
        /** @var list<string> $keys */
        $keys = $ids->getIds();
        $aggregations = $this->productRepository->aggregate($criteria, $context);

        // no products found, no need to continue
        if (empty($keys)) {
            return new EntitySearchResult(
                ProductDefinition::ENTITY_NAME,
                0,
                new ProductCollection(),
                $aggregations,
                $origin,
                $context->getContext()
            );
        }

        $mapping = array_combine($keys, $keys);

        $hasOptionFilter = $this->hasOptionFilter($criteria);

        $event = new ProductListingResolvePreviewEvent($context, $criteria, $mapping, $hasOptionFilter);
        $this->eventDispatcher->dispatch($event);
        $mapping = $event->getMapping();

        $read = $criteria->cloneForRead(array_values($mapping));
        $read->addAssociation('options.group');

        $searchResult = $this->productRepository->search($read, $context);

        $this->addExtensions($ids, $searchResult, $mapping);

        $result = new EntitySearchResult(ProductDefinition::ENTITY_NAME, $ids->getTotal(), $searchResult->getEntities(), $aggregations, $origin, $context->getContext());
        $result->addState(...$ids->getStates());

        return $result;
    }

    private function hasOptionFilter(Criteria $criteria): bool
    {
        $filters = $criteria->getPostFilters();

        $fields = [];
        foreach ($filters as $filter) {
            array_push($fields, ...$filter->getFields());
        }

        $fields = array_map(fn (string $field) => preg_replace('/^product./', '', $field), $fields);

        if (\in_array('options.id', $fields, true)) {
            return true;
        }

        if (\in_array('optionIds', $fields, true)) {
            return true;
        }

        return false;
    }

    private function handleAvailableStock(Criteria $criteria, SalesChannelContext $context): void
    {
        $salesChannelId = $context->getSalesChannel()->getId();

        $hide = $this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId);

        if (!$hide) {
            return;
        }

        $closeoutFilter = $this->productCloseoutFilterFactory->create($context);
        $criteria->addFilter($closeoutFilter);
    }

    /**
     * @param EntitySearchResult<ProductCollection> $entities
     * @param array<string> $mapping
     */
    private function addExtensions(IdSearchResult $ids, EntitySearchResult $entities, array $mapping): void
    {
        foreach ($ids->getExtensions() as $name => $extension) {
            $entities->addExtension($name, $extension);
        }

        /** @var string $id */
        foreach ($ids->getIds() as $id) {
            if (!isset($mapping[$id])) {
                continue;
            }

            // current id was mapped to another variant
            if (!$entities->has($mapping[$id])) {
                continue;
            }

            /** @var Entity $entity */
            $entity = $entities->get($mapping[$id]);

            // get access to the data of the search result
            $entity->addExtension('search', new ArrayEntity($ids->getDataOfId($id)));
        }
    }
}
