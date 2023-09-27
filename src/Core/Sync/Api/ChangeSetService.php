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

namespace Elio\ElioSearch\Core\Sync\Api;

use Elio\ElioSearch\Core\Sync\Api\Indexer\IndexerInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

class ChangeSetService
{
    public const KEY_CREATED = 'created';
    public const KEY_UPDATED = 'updated';
    public const KEY_DELETED = 'deleted';

    public function __construct(
        private readonly EntityRepository $entityStatusRepository,
        private readonly iterable $indexers
    )
    {
    }

    /**
     * Prepares change set array
     *
     * @param string $dataType
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @return array
     */
    public function changeSet(string $dataType, SyncProfileEntity $syncProfile, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', $dataType));
        if ($syncProfile->getLastGenerationFinishedAt()) {
            $criteria->addFilter(new OrFilter([
                new RangeFilter('createdAt', [
                    RangeFilter::GTE => $syncProfile->getLastGenerationFinishedAt()
                        ->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]),
                new RangeFilter('updatedAt', [
                    RangeFilter::GTE => $syncProfile->getLastGenerationFinishedAt()
                        ->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]),
            ]));
        }
        $iterator = new RepositoryIterator($this->entityStatusRepository, $context, $criteria);
        $changeSet = [];
        while ($entityStatuses = $iterator->fetch()) {
            /** @var EntityStatusEntity $entityStatus */
            foreach ($entityStatuses as $entityStatus) {
                if ($entityStatus->getState() === EntityStatusEntity::STATE_CREATED) {
                    $changeSet[self::KEY_CREATED][] = $entityStatus->getId();
                }

                if ($entityStatus->getState() === EntityStatusEntity::STATE_UPDATED) {
                    $changeSet[self::KEY_UPDATED][] = $entityStatus->getId();
                }

                if ($entityStatus->getState() === EntityStatusEntity::STATE_DELETED) {
                    $changeSet[self::KEY_DELETED][] = $entityStatus->getId();
                }
            }
        }

        return $changeSet;
    }

    /**
     * Indexing updated entries
     *
     * @param Context $context
     * @return void
     */
    public function index(Context $context): void
    {
        $entitiesStatus = $this->entityStatusRepository->search(new Criteria(), $context);
        /** @var IndexerInterface $indexer */
        foreach ($this->indexers as $indexer) {
            $entityStatuses = $indexer->index($context, $entitiesStatus->getEntities());
            $this->saveStatusData($entityStatuses, $context);
        }
    }

    /**
     * Saves status data
     *
     * @param EntityStatusCollection $entityStatuses
     * @param Context $context
     * @return void
     */
    protected function saveStatusData(
        EntityStatusCollection $entityStatuses,
        Context $context
    ): void {
        $upsertData = [];
        /** @var EntityStatusEntity $entityStatus */
        foreach ($entityStatuses as $entityStatus) {
            $upsertData[] = [
                'id' => $entityStatus->getId(),
                'state' => $entityStatus->getState(),
                'type' => $entityStatus->getType(),
                'hashedContent' => $entityStatus->getHashedContent(),
                'deletedAt' => $entityStatus->getDeletedAt(),
            ];
        }

        foreach (array_chunk($upsertData, 500) as $chunk) {
            $this->entityStatusRepository->upsert($chunk, $context);
        }
    }
}