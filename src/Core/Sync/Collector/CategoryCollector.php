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

namespace Elio\ElioDataDiscovery\Core\Sync\Collector;

use Elio\ElioDataDiscovery\Core\Sync\Collector\Event\CriteriaPreparedEvent;
use Elio\ElioDataDiscovery\Core\Sync\Collector\Event\DataCollectedEvent;
use Elio\ElioDataDiscovery\Core\Sync\DataTypes\ContentDataType;
use Elio\ElioDataDiscovery\Core\Sync\SalesChannelContextCollection;
use Elio\ElioDataDiscovery\Core\Sync\Translator\TranslatorAware;
use Elio\ElioDataDiscovery\Core\Sync\Util\CategoryUtil;
use Elio\ElioDataDiscovery\ElioDataDiscovery;
use Generator;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Struct\Collection;
use Shopware\Core\Framework\Struct\StructCollection;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\NavigationPageSeoUrlRoute;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\ProductPageSeoUrlRoute;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Elio\ElioDataDiscovery\Core\Sync\Output\SeoRoute;

/**
 * Class CategoryCollector
 * @package Elio\ElioDataDiscovery\Core\Sync\Collector
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class CategoryCollector implements DataCollectorInterface
{
    use TranslatorAware;
    public const TYPE = ContentDataType::class;
    public const CHUNK_SIZE = 500;
    private array $customFields = [];

    public function __construct(
        private readonly SalesChannelRepository $categoryRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Checks if collector is supported
     *
     * @param string $type
     * @param string|null $entityType
     * @return bool
     */
    public function supports(string $type, ?string $entityType = null): bool
    {
        if ($entityType && $entityType !== CategoryDefinition::ENTITY_NAME) {
            return false;
        }

        return self::TYPE === $type;
    }

    /**
     * Collects translated data for category
     *
     * @param SalesChannelContextCollection $contexts
     * @param Criteria|null $criteria
     * @return Generator<Collection>
     */
    public function collect(SalesChannelContextCollection $contexts, ?Criteria $criteria = null): Generator
    {
        $result = CategoryUtil::buildCustomFieldInheritance($this->categoryRepository, $contexts->getFirst());
        if (array_key_exists('customFields', $result) && is_array($result['customFields'])) {
            $this->customFields = $result['customFields'];
        }

        $criteria = $criteria ? clone $criteria : new Criteria();
        $this->prepareCriteria($criteria);
        $context = $contexts->getFirst();
        $categoryIds = $this->categoryRepository->searchIds($criteria, $context)->getIds();
        foreach (array_chunk($categoryIds, self::CHUNK_SIZE) as $chunk) {
            $criteria->setIds($chunk);
            $data = $this->prepareTranslationData($contexts, $criteria, $this->categoryRepository);
            yield $this->mapCollectedData($data);
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
        $criteria->addAssociation('media');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('productStream');
        $criteria->addAssociation('cmsPage');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('translations');
        //$criteria->addAssociation('products'); <== Commented out due to OutOfMemoryError; should be implemented elsewhere in the future
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('visible', true));

        $criteria->addFilter(new OrFilter([
            new EqualsFilter('customFields.' . ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE, false),
            new EqualsFilter('customFields.' . ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE, null)
        ]));

        $criteria->addFilter(new OrFilter([
            new EqualsFilter('customFields.' . ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE, false),
            new EqualsFilter('customFields.' . ElioDataDiscovery::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE, null)
        ]));

        $event = new CriteriaPreparedEvent(self::TYPE, $criteria);
        $this->dispatcher->dispatch($event);

        return $event->getCriteria();
    }

    /**
     * Maps collected data to dataType
     *
     * @param array $data
     * @return Collection
     */
    protected function mapCollectedData(array $data): Collection
    {
        $mappedEntities = new StructCollection();
        foreach ($data as $languageId => $entities) {
            /** @var CategoryEntity $entity */
            foreach ($entities as $entity) {
                $dataType = $this->mapCategoryToDataType($entity);
                /** @var ContentDataType $mappedEntity */
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
     * Maps category data to content type
     *
     * @param CategoryEntity $category
     * @return ContentDataType
     */
    protected function mapCategoryToDataType(CategoryEntity $category): ContentDataType
    {
        $contentType = new ContentDataType();
        $contentType->setId($category->getId());
        $contentType->setName($category->getName());
        $contentType->setType($category->getType());
        $contentType->setDescription($category->getDescription());
        $contentType->setTitle($category->getMetaTitle());
        $contentType->setSeoText($category->getMetaDescription());
        $contentType->setSeoUrls($category->getSeoUrls());
        $contentType->setKeywords($category->getKeywords());
        $contentType->setMedia($category->getMedia());
        $contentType->setCreatedAt($category->getCreatedAt());
        $contentType->setBreadcrumb($category->getBreadcrumb());
        $contentType->setTags($category->getTags());
        $contentType->setCustomFields($this->customFields[$category->getId()] ?? $category->getCustomFields());
        $contentType->addExtension('original', $category);
        $contentType->addExtension(SeoRoute::class, new SeoRoute(
            NavigationPageSeoUrlRoute::ROUTE_NAME, $contentType->getId(), ['navigationId' => $contentType->getId()]
        ));
        return $contentType;
    }
}