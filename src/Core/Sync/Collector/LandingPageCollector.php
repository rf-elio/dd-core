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

use Elio\ElioSearch\Core\Sync\Collector\Event\CriteriaPreparedEvent;
use Elio\ElioSearch\Core\Sync\Collector\Event\DataCollectedEvent;
use Elio\ElioSearch\Core\Sync\DataTypes\ContentDataType;
use Elio\ElioSearch\Core\Sync\SalesChannelContextCollection;
use Elio\ElioSearch\Core\Sync\Translator\TranslatorAware;
use Elio\ElioSearch\ElioSearch;
use Generator;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\LandingPage\LandingPageEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class LandingPageCollector
 * @package Elio\ElioSearch\Core\Sync\Collector
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class LandingPageCollector implements DataCollectorInterface
{
    use TranslatorAware;
    public const TYPE = ContentDataType::class;
    public const CHUNK_SIZE = 500;

    public function __construct(
        private readonly SalesChannelRepository $landingPageRepository,
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
        if ($entityType && $entityType !== LandingPageDefinition::ENTITY_NAME) {
            return false;
        }

        return self::TYPE === $type;
    }

    /**
     * Collects translated data for landing page
     *
     * @param SalesChannelContextCollection $contexts
     * @param Criteria|null $criteria
     * @return Generator<EntityCollection>
     */
    public function collect(SalesChannelContextCollection $contexts, ?Criteria $criteria = null): Generator
    {
        $criteria = $criteria ? clone $criteria : new Criteria();
        $context = $contexts->getFirst();
        $this->prepareCriteria($criteria, $context->getSalesChannelId());
        $landingPageIds = $this->landingPageRepository->searchIds($criteria, $context)->getIds();
        foreach (array_chunk($landingPageIds, self::CHUNK_SIZE) as $chunk) {
            $criteria->setIds($chunk);
            $data = $this->prepareTranslationData($contexts, $criteria, $this->landingPageRepository);
            yield $this->mapCollectedData($data);
        }
    }

    /**
     * Adds default filter and associations to criteria
     *
     * @param Criteria $criteria
     * @param string $salesChannelId
     * @return Criteria
     */
    protected function prepareCriteria(Criteria $criteria, string $salesChannelId): Criteria
    {
        $criteria->addAssociation('salesChannels');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('cmsPage');
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('salesChannels.id', $salesChannelId));

        $criteria->addFilter(new OrFilter([
            new EqualsFilter('customFields.' . ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE, false),
            new EqualsFilter('customFields.' . ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE, null)
        ]));

        $criteria->addFilter(new OrFilter([
            new EqualsFilter('customFields.' . ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE, false),
            new EqualsFilter('customFields.' . ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE, null)
        ]));

        $event = new CriteriaPreparedEvent(self::TYPE, $criteria);
        $this->dispatcher->dispatch($event);

        return $event->getCriteria();
    }

    /**
     * Maps collected data to dataType
     *
     * @param array $data
     * @return EntityCollection
     */
    protected function mapCollectedData(array $data): EntityCollection
    {
        $mappedEntities = new EntityCollection();
        foreach ($data as $languageId => $entities) {
            /** @var LandingPageEntity $entity */
            foreach ($entities as $entity) {
                $dataType = $this->mapLandingPageToDataType($entity);
                /** @phpstan-ignore-next-line */
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
     * Maps landing page data to content type
     *
     * @param LandingPageEntity $landingPage
     * @return ContentDataType
     */
    protected function mapLandingPageToDataType(LandingPageEntity $landingPage): ContentDataType
    {
        $contentType = new ContentDataType();
        $contentType->setId($landingPage->getId());
        $contentType->setName($landingPage->getName());
        $contentType->setTitle($landingPage->getMetaTitle());
        $contentType->setSeoText($landingPage->getMetaDescription());
        $contentType->setSeoUrls($landingPage->getSeoUrls());
        $contentType->setKeywords($landingPage->getKeywords());
        $contentType->setCreatedAt($landingPage->getCreatedAt());
        $contentType->setTags($landingPage->getTags());
        $contentType->setCustomFields($landingPage->getCustomFields());
        $contentType->addExtension('original', $landingPage);
        return $contentType;
    }
}