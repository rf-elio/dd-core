<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\ElioSearch\Core\Sync\Collector;

use Elio\ElioSearch\Configuration\Configuration;
use Elio\ElioSearch\Configuration\ElioSearchConfigService;
use Elio\ElioSearch\Core\Sync\DataTypes\Aggregation\Variant;
use Elio\ElioSearch\Core\Sync\Collector\Event\CriteriaPreparedEvent;
use Elio\ElioSearch\Core\Sync\Collector\Event\DataCollectedEvent;
use Elio\ElioSearch\Core\Sync\DataTypes\ProductDataType;
use Elio\ElioSearch\Core\Sync\Output\SeoRoute;
use Elio\ElioSearch\Core\Sync\SalesChannelContextCollection;
use Elio\ElioSearch\Core\Sync\Translator\TranslatorAware;
use Elio\ElioSearch\Core\Sync\Util\ProductUtil;
use Elio\ElioSearch\ElioSearch;
use Generator;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingEntity;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\ProductPageSeoUrlRoute;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProductCollector
 * @package Elio\ElioSearch\Core\Sync\Collector
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductCollector implements DataCollectorInterface
{
    use TranslatorAware;

    public const TYPE = ProductDataType::class;
    public const CHUNK_SIZE = 100;

    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly SalesChannelRepository $categoryRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ElioSearchConfigService $configService
    ) {
    }

    /**
     * Checks if collector is supported
     *
     * @param string $type
     * @param string|null $entityType
     * @return bool
     */
    public function supports(string $type, ?string $entityType = null): bool
    {
        if ($entityType && $entityType !== ProductDefinition::ENTITY_NAME) {
            return false;
        }

        return self::TYPE === $type;
    }

    /**
     * Collects translated data for products
     *
     * @param SalesChannelContextCollection $contexts
     * @param Criteria|null $criteria
     * @return Generator<EntityCollection>
     */
    public function collect(SalesChannelContextCollection $contexts, ?Criteria $criteria = null): Generator
    {
        $categories = $this->loadCategories($contexts);
        $criteria = $criteria ? clone $criteria : new Criteria();
        $this->prepareCriteria($criteria);
        $context = $contexts->getFirst();
        $config = $this->configService->getByContext($context);
        $productIds = $this->productRepository->searchIds($criteria, $context)->getIds();
        foreach (array_chunk($productIds, self::CHUNK_SIZE) as $chunk) {
            $criteria->setIds($chunk);
            $data = $this->prepareTranslationData($contexts, $criteria, $this->productRepository);
            $parentProducts = $this->loadParentProducts($data, $context);
            yield $this->mapCollectedData($data, $parentProducts, $categories, $config);
        }
    }

    /**
     * Adds default filter and associations to criteria
     *
     * @param Criteria $criteria
     * @return Criteria
     */
    protected function prepareCriteria(Criteria $criteria): Criteria
    {
        $criteria->addAssociation('manufacturer.media');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('configuratorSettings');
        $criteria->addAssociation('elioSearchProductSorting');

        $event = new CriteriaPreparedEvent(self::TYPE, $criteria);
        $this->dispatcher->dispatch($event);

        return $event->getCriteria();
    }

    /**
     * @param array $data
     * @param SalesChannelContext $context
     * @return EntityCollection<SalesChannelProductEntity>
     */
    protected function loadParentProducts(array $data, SalesChannelContext $context): EntityCollection
    {
        $parentProductIds = [];
        foreach ($data as $entities) {
            /** @var SalesChannelProductEntity $entity */
            foreach ($entities as $entity) {
                $parentProductIds[] = $entity->getParentId();
            }
        }

        $parentProductIds = array_filter($parentProductIds);
        $parentProductIds = array_unique($parentProductIds);

        if (empty($parentProductIds)) {
            return new EntityCollection();
        }

        $criteria = new Criteria($parentProductIds);
        $criteria->addAssociation('children'); // children are required for ProductUtil::isDisplayedByDefault
        $criteria->addAssociation('configuratorSettings');
        return $this->productRepository->search($criteria, $context);

    }

    /**
     * Maps collected data to dataType
     *
     * @param array $data
     * @param EntityCollection<SalesChannelProductEntity> $parentProducts
     * @param CategoryCollection[] $categories
     * @param Configuration $config
     * @return EntityCollection
     */
    protected function mapCollectedData(
        array $data,
        EntityCollection $parentProducts,
        array $categories,
        Configuration $config
    ): EntityCollection {
        /** @var EntityCollection<ProductDataType> $mappedEntities */
        $mappedEntities = new EntityCollection();

        foreach ($data as $languageId => $entities) {
            $flatCategoryCollection = $categories[$languageId];

            /** @var SalesChannelProductEntity $entity */
            foreach ($entities as $entity) {
                $productCategoryCollection = $entity->getCategories() ?? new CategoryCollection();
                $entity->setCategories($productCategoryCollection);
                foreach ($entity->getCategoryIds() ?? [] as $categoryId) {
                    if ($category = $flatCategoryCollection->get($categoryId)) {
                        $productCategoryCollection->add($category);
                    }
                }

                $dataType = ProductDataType::createFrom($entity);
                $dataType->setVariant(new Variant());
                $dataType->addExtension(SeoRoute::class, new SeoRoute(
                    ProductPageSeoUrlRoute::ROUTE_NAME, $dataType->getId(), ['productId' => $dataType->getId()]
                ));
                $dataType->setThumbnailUrl(ProductUtil::getThumbnailUrl(
                    $dataType->getCover()?->getMedia()?->getThumbnails(), $config->getSuggestThumbnailSize()
                ));
                if (
                    $entity->getParentId()
                    && $parentProduct = ProductDataType::createFrom($parentProducts->get($entity->getParentId()))
                ) {
                    $parentProduct->setIdentifier($parentProduct->getProductNumber());
                    $dataType->getVariant()->setParentProduct($parentProduct);
                }
                $dataType->getVariant()->setGroupingKey(
                    ProductUtil::getGroupingKey($entity, $dataType->getVariant()->getParentProduct())
                );
                $displayByDefault = ProductUtil::isDisplayedByDefault($entity, $dataType->getVariant()->getParentProduct());
                if ($entity->getCustomFields()[ElioSearch::CUSTOM_FIELD_DISPLAY_PRODUCT_BY_DEFAULT] ?? false) {
                    $displayByDefault = true;
                }
                $dataType->getVariant()->setDisplayByDefault($displayByDefault);

                /** @var ProductConfiguratorSettingEntity|null $productConfiguratorSettings */
                $productConfiguratorSettings = $entity->getConfiguratorSettings()?->first() ?? $dataType->getVariant()->getParentProduct()?->getConfiguratorSettings()?->first();
                if ($productConfiguratorSettings) {
                    $position = $productConfiguratorSettings->getPosition();

                    if ($displayByDefault) {
                        $position = -10000 + $position;
                    }

                    $dataType->getVariant()->setPosition($position);
                } elseif ($displayByDefault) {
                    $dataType->getVariant()->setPosition(-1);
                }

                $mappedEntity = $mappedEntities->get($dataType->getId()) ?? $dataType;
                $mappedEntity->addDataTypeTranslation($languageId, $dataType);
                $mappedEntities->set($dataType->getId(), $mappedEntity);
            }
        }

        $event = new DataCollectedEvent(self::TYPE, $mappedEntities);
        $this->dispatcher->dispatch($event);
        return $event->getData();
    }

    /**
     * @param SalesChannelContextCollection $contexts
     * @return CategoryCollection[]
     */
    protected function loadCategories(SalesChannelContextCollection $contexts): array
    {
        $categories = [];
        /** @var SalesChannelContext $context */
        foreach ($contexts as $context) {
            $flatCategoryCollection = new CategoryCollection();
            $criteria = new Criteria([$context->getSalesChannel()->getNavigationCategoryId()]);
            foreach ($this->categoryRepository->search($criteria, $context) as $categoryEntity) {
                $flatCategoryCollection->add($categoryEntity);
                $this->loadChildCategories($categoryEntity, $flatCategoryCollection, $context);
            }

            $categories[$context->getLanguageId()] = $flatCategoryCollection;
        }
        return $categories;
    }

    protected function loadChildCategories(
        CategoryEntity $parentCategoryEntity,
        CategoryCollection $flatCategoryCollection,
        SalesChannelContext $context
    ): void {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('parentId', $parentCategoryEntity->getId()));
        /** @var CategoryEntity $categoryEntity */
        foreach ($this->categoryRepository->search($criteria, $context) as $categoryEntity) {
            $flatCategoryCollection->add($categoryEntity);
            $this->loadChildCategories($categoryEntity, $flatCategoryCollection, $context);
        }
    }
}
