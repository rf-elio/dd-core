<?php
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

namespace Elio\ElioSearch\Core\Sync\Input;

use Elio\ElioSearch\Core\Sync\ChangeSet\ChangeSetService;
use Elio\ElioSearch\Core\Sync\ChangeSet\EntityStatusCollection;
use Elio\ElioSearch\Core\Sync\ChangeSet\EntityStatusEntity;
use Elio\ElioSearch\Core\Sync\DataTypes\ContentDataType;
use Elio\ElioSearch\Core\Sync\DataTypes\DataTypeInterface;
use Elio\ElioSearch\Core\Sync\DataTypes\Exception\UnknownContentTypeException;
use Elio\ElioSearch\Core\Sync\DataTypes\ProductDataType;
use Elio\ElioSearch\Core\Sync\DeltaDataCollection;
use Elio\ElioSearch\Core\Sync\SyncContext;
use Generator;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Collection;

/**
 * Class DeltaInput
 * @package Elio\ElioSearch\Core\Sync\Input
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class DeltaInput extends BaseInput
{
    public const TYPE = self::class;

    public function __construct(
        private readonly ChangeSetService $changeSetService,
        private readonly iterable $collectors,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($collectors);
    }

    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @param SyncContext $syncContext
     * @return Generator<DeltaDataCollection>
     */
    public function read(SyncContext $syncContext): Generator
    {
        $syncProfile = $syncContext->getSyncProfile();
        $contexts = $syncContext->getSalesChannelContexts();
        $changeSet = $this->changeSetService->getChangeSet(
            $syncProfile,
            $syncContext->getSalesChannelContexts()->getFirst()->getContext()
        );

        $this->logger->info('DeltaInput: ChangeSet count', [
            'created' => $changeSet->countCreated(),
            'updated' => $changeSet->countUpdated(),
            'deleted' => $changeSet->countDeleted(),
        ]);

        if ($changeSet->isEmpty()) {
            $this->logger->info(sprintf('DeltaInput: No entries sync entries found for profile %s',
                $syncContext->getProfileDefinition()->getName()));
            return;
        }

        foreach ($changeSet->getDeleted() as $changeSetEntityCollection) {
            $deltaDataCollection = new DeltaDataCollection(DeltaDataCollection::TYPE_DELETED, []);
            /** @var EntityStatusEntity $changeSetEntity */
            foreach ($changeSetEntityCollection as $changeSetEntity) {
                if ($changeSetEntity->getDataType() === ProductDataType::class) {
                    $entity = new ProductDataType();
                    $entity->setId($changeSetEntity->getEntityId());
                    $entity->setIdentifier($changeSetEntity->getIdentifier());
                    $entity->setDeletedAt($changeSetEntity->getDeletedAt());
                    $deltaDataCollection->add($entity);
                } elseif ($changeSetEntity->getDataType() === ContentDataType::class) {
                    $entity = new ContentDataType();
                    $entity->setId($changeSetEntity->getEntityId());
                    $entity->setIdentifier($changeSetEntity->getIdentifier());
                    $entity->setDeletedAt($changeSetEntity->getDeletedAt());
                    $deltaDataCollection->add($entity);
                } else {
                    throw new UnknownContentTypeException(sprintf(
                        'DataType "%s" given, but not supported', $changeSetEntity->getDataType()
                    ));
                }
            }
            yield $deltaDataCollection;
        }

        foreach ($changeSet->getCreated() as $entityType => $changeSetEntityCollection) {
            $criteria = new Criteria($changeSetEntityCollection->getEntityIds());
            foreach ($this->getCollectors($syncProfile->getDataType(), $entityType) as $collector) {
                foreach ($collector->collect($contexts, $criteria) as $collection) {
                    $this->mapEntityStatusBaseFields($changeSetEntityCollection, $collection);
                    yield new DeltaDataCollection(DeltaDataCollection::TYPE_CREATED, $collection);
                }
            }
        }

        foreach ($changeSet->getUpdated() as $entityType => $changeSetEntityCollection) {
            $criteria = new Criteria($changeSetEntityCollection->getEntityIds());
            foreach ($this->getCollectors($syncProfile->getDataType(), $entityType) as $collector) {
                foreach ($collector->collect($contexts, $criteria) as $collection) {
                    $this->mapEntityStatusBaseFields($changeSetEntityCollection, $collection);
                    yield new DeltaDataCollection(DeltaDataCollection::TYPE_UPDATED, $collection);
                }
            }
        }
    }

    protected function mapEntityStatusBaseFields(
        EntityStatusCollection $entityStatusCollection,
        Collection $entities
    ): void {
        $entityStatusCollection->fmap(function (EntityStatusEntity $entityStatusEntity) use ($entities) {
            /** @var DataTypeInterface|null $dataTypeEntity */
            $dataTypeEntity = $entities->get($entityStatusEntity->getEntityId());
            $dataTypeEntity?->setIdentifier($entityStatusEntity->getIdentifier());
        });
    }
}