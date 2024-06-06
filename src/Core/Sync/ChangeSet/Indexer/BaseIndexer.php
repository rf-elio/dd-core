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

namespace Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer;

use DateTimeImmutable;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\EntityStatusCollection;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\EntityStatusEntity;
use JsonException;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\SalesChannelRepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class BaseIndexer
 * @package Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseIndexer implements IndexerInterface
{
    public function __construct(
        private readonly string $dataType,
        private readonly string $entityType,
        private readonly SalesChannelRepository $repository
    ) {}

    /**
     * Gets criteria
     *
     * @param SalesChannelContext $salesChannelContext
     * @return Criteria
     */
    abstract protected function getCriteria(SalesChannelContext $salesChannelContext): Criteria;

    /**
     * Extracts the entity identifier (e.g. product number)
     *
     * @param Struct $entity
     * @return string
     */
    abstract protected function getEntityIdentifier(Struct $entity): string;

    /**
     * Provides the unique indexer identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * Indexing entity states
     *
     * @param EntityStatusCollection $currentEntityStatusCollection
     * @param SalesChannelContext $context
     * @return EntityStatusCollection
     * @throws JsonException
     */
    public function index(EntityStatusCollection $currentEntityStatusCollection, SalesChannelContext $context): EntityStatusCollection
    {
        $criteria = $this->getCriteria($context);
        $criteria->setOffset(0);
        $criteria->setLimit(50);
        $iterator = new SalesChannelRepositoryIterator($this->repository, $context, $criteria);
        $filteredEntityStatusCollection = $currentEntityStatusCollection->filterByProperty('entityType', $this->entityType);
        $newEntityStatusCollection = new EntityStatusCollection();

        while ($entities = $iterator->fetch()) {
            foreach ($entities as $entity) {
                $entityStatus = $filteredEntityStatusCollection->getEntityStatus(
                    $entity->getApiAlias(), $this->getEntityIdentifier($entity)
                ) ?? new EntityStatusEntity();

                $changed = $this->prepareEntityStatusEntity(
                    $entityStatus,
                    $entity,
                    $context,
                );

                if ($changed) {
                    $newEntityStatusCollection->add($entityStatus);
                }
                $filteredEntityStatusCollection->remove($entityStatus->getId());
            }
        }

        // all note removed entities are deleted
        /** @var EntityStatusEntity $item */
        foreach ($filteredEntityStatusCollection as $item) {
            if (!$item->getDeletedAt()) {
                $this->setDeleted($item);
                $newEntityStatusCollection->add($item);
            }
        }

        return $newEntityStatusCollection;
    }

    /**
     * Hash entity data
     *
     * @param Struct $struct
     * @return string
     * @throws JsonException
     */
    protected function hash(Struct $struct): string
    {
        return md5(json_encode($struct, JSON_THROW_ON_ERROR));
    }

    /**
     * @param EntityStatusEntity $entityStatus
     * @param Struct $entity
     * @param SalesChannelContext $context
     * @return bool
     * @throws JsonException
     */
    protected function prepareEntityStatusEntity(
        EntityStatusEntity $entityStatus,
        Struct $entity,
        SalesChannelContext $context
    ): bool {
        $changed = false;
        $hash = $this->hash($entity);

        if (!$entityStatus->hasId()) {
            $entityStatus->setId(Uuid::randomHex());
            $entityStatus->setState(EntityStatusEntity::STATE_CREATED);
            $changed = true;
        } elseif ($entityStatus->getDeletedAt() !== null) {
            $entityStatus->setDeletedAt(null);
            $entityStatus->setState(EntityStatusEntity::STATE_CREATED);
            $changed = true;
        } elseif ($entityStatus->getHash() !== $hash) {
            $entityStatus->setState(EntityStatusEntity::STATE_UPDATED);
            $changed = true;
        }

        $entityStatus->setEntityType($entity->getApiAlias());
        if (method_exists($entity, 'getId')) {
            $entityStatus->setEntityId($entity->getId());
        }
        $entityStatus->setIdentifier($this->getEntityIdentifier($entity));
        $entityStatus->setSalesChannelId($context->getSalesChannelId());
        $entityStatus->setDataType($this->dataType);
        $entityStatus->setHash($hash);
        return $changed;
    }

    /**
     * @param EntityStatusEntity $entityStatus
     * @return void
     */
    protected function setDeleted(
        EntityStatusEntity $entityStatus
    ): void {
        $entityStatus->setState(EntityStatusEntity::STATE_DELETED);
        $entityStatus->setDeletedAt(new DateTimeImmutable());
    }
}
