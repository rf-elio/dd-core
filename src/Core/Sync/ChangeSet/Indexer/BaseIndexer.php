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

namespace Elio\ElioSearch\Core\Sync\ChangeSet\Indexer;

use DateTimeImmutable;
use Elio\ElioSearch\Core\Sync\ChangeSet\EntityStatusCollection;
use Elio\ElioSearch\Core\Sync\ChangeSet\EntityStatusEntity;
use JsonException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class BaseIndexer
 * @package Elio\ElioSearch\Core\Sync\ChangeSet\Indexer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseIndexer implements IndexerInterface
{
    public function __construct(
        private readonly string $type,
        private readonly EntityRepository $repository
    ) {
    }

    /**
     * Gets criteria
     *
     * @param Context $context
     * @return Criteria
     */
    abstract protected function getCriteria(Context $context): Criteria;

    /**
     * Extracts the entity identifier (e.g. product number)
     *
     * @param Struct $entity
     * @return string
     */
    abstract protected function getEntityIdentifier(Struct $entity): string;

    /**
     * Checks if indexer is supported
     *
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Indexing entity states
     *
     * @param EntityStatusCollection $currentEntityStatusCollection
     * @param Context $context
     * @return EntityStatusCollection
     * @throws JsonException
     */
    public function index(EntityStatusCollection $currentEntityStatusCollection, Context $context): EntityStatusCollection
    {
        $criteria = $this->getCriteria($context);
        $iterator = new RepositoryIterator($this->repository, $context, $criteria);
        $newEntityStatusCollection = new EntityStatusCollection();

        while ($entities = $iterator->fetch()) {
            foreach ($entities as $entity) {
                $entityStatus = $currentEntityStatusCollection->getEntityStatus(
                    $entity->getApiAlias(), $this->getEntityIdentifier($entity)
                ) ?? new EntityStatusEntity();

                $changed = $this->prepareEntityStatusEntity(
                    $entityStatus,
                    $entity,
                );

                if ($changed) {
                    $newEntityStatusCollection->add($entityStatus);
                }
                $currentEntityStatusCollection->remove($entityStatus->getId());
            }
        }

        // all note removed entities are deleted
        /** @var EntityStatusEntity $item */
        foreach ($currentEntityStatusCollection as $item) {
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

    protected function prepareEntityStatusEntity(
        EntityStatusEntity $entityStatus,
        Struct $entity
    ): bool {
        $changed = false;
        $hash = $this->hash($entity);

        if (!$entityStatus->hasId()) {
            $entityStatus->setId(Uuid::randomHex());
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
        $entityStatus->setDataType($this->type);
        $entityStatus->setHash($hash);
        return $changed;
    }

    protected function setDeleted(
        EntityStatusEntity $entityStatus
    ): void {
        $entityStatus->setState(EntityStatusEntity::STATE_DELETED);
        $entityStatus->setDeletedAt(new DateTimeImmutable());
    }
}