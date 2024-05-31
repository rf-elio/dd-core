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

namespace Elio\ElioDataDiscovery\Core\Sync\ChangeSet;

use DateTimeImmutable;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer\IndexerInterface;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Message\AsyncIndexUpdateMessage;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Message\IndexUpdateMessage;
use Elio\ElioDataDiscovery\Core\Sync\SyncProfileEntity;
use Elio\ElioDataDiscovery\Core\Sync\SyncService;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Messenger\MessageBusInterface;

class ChangeSetService
{
    public const KEY_CREATED = 'created';
    public const KEY_UPDATED = 'updated';
    public const KEY_DELETED = 'deleted';

    public function __construct(
        private readonly EntityRepository $entityStatusRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly iterable $indexers,
        private readonly LoggerInterface $logger,
        private readonly SyncService $syncService
    ) {}

    /**
     * Prepares change set array
     *
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @param bool $fullSync
     * @return ChangeSet
     */
    public function getChangeSet(SyncProfileEntity $syncProfile, Context $context, bool $fullSync): ChangeSet
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('dataType', $syncProfile->getDataType()));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $syncProfile->getSalesChannel()?->getId()));
        if (!$fullSync && $syncProfile->getLastGenerationStartedAt()) {
            $criteria->addFilter(new OrFilter([
                new RangeFilter('createdAt', [
                    RangeFilter::GTE => $syncProfile->getLastGenerationStartedAt()
                        ->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]),
                new RangeFilter('updatedAt', [
                    RangeFilter::GTE => $syncProfile->getLastGenerationStartedAt()
                        ->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]),
            ]));
        }

        $iterator = new RepositoryIterator($this->entityStatusRepository, $context, $criteria);
        $changeSet = new ChangeSet();
        while ($entityStatuses = $iterator->fetch()) {
            /** @var EntityStatusEntity $entityStatus */
            foreach ($entityStatuses as $entityStatus) {
                if ($entityStatus->getState() === EntityStatusEntity::STATE_DELETED) {
                    $changeSet->addDeleted($entityStatus);
                    continue;
                }

                if (!$syncProfile->getLastGenerationStartedAt()) {
                    $changeSet->addCreated($entityStatus);
                    continue;
                }

                if ($entityStatus->getCreatedAt() > $syncProfile->getLastGenerationStartedAt()) {
                    $changeSet->addCreated($entityStatus);
                } else {
                    $changeSet->addUpdated($entityStatus);
                }
            }
        }

        return $changeSet;
    }

    /**
     * Indexing updated entries
     *
     * @param Context $context
     * @param bool $isAsync
     * @return void
     */
    public function startIndexing(Context $context, bool $isAsync = false): void
    {
        $this->logger->info('Changeset: Indexing started');
        $salesChannelContexts = $this->syncService->getSalesChannelContexts($context);

        foreach ($salesChannelContexts as $salesChannelContext) {
            $this->logger->info(sprintf(
                'Changeset: Indexing sales channel %s',
                $salesChannelContext->getSalesChannelId())
            );
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
            $entitiesStatus = $this->entityStatusRepository->search($criteria, $context);
            /** @var EntityStatusCollection $currentEntityStatusCollection */
            $currentEntityStatusCollection = $entitiesStatus->getEntities();

            /** @var IndexerInterface $indexer */
            foreach ($this->indexers as $indexer) {
                $this->logger->info('Changeset: Dispatch IndexUpdateMessage', [
                    'indexer' => $indexer->getIdentifier()
                ]);

                if (!$isAsync) {
                    $this->index(
                        $indexer->getIdentifier(),
                        $currentEntityStatusCollection,
                        $salesChannelContext
                    );
                } else {
                    $message = AsyncIndexUpdateMessage::create(
                        $indexer->getIdentifier(),
                        $salesChannelContext,
                        $currentEntityStatusCollection
                    );
                    $this->messageBus->dispatch($message);
                }
            }
        }
    }

    public function index(
        string $indexerIdentifier,
        EntityStatusCollection $entityStatusCollection,
        SalesChannelContext $context
    ): void
    {
        /** @var IndexerInterface $indexer */
        foreach ($this->indexers as $indexer) {
            if ($indexer->getIdentifier() !== $indexerIdentifier) {
                continue;
            }

            $this->logger->info('Changeset: Indexer start', [
                'indexer' => $indexerIdentifier
            ]);
            $entityStatuses = $indexer->index($entityStatusCollection, $context);
            $this->persistEntityStatusCollection($entityStatuses, $context->getContext());
            $this->logger->info('Changeset: Indexer done', [
                'indexer' => $indexerIdentifier,
                'changes' => $entityStatuses->count()
            ]);
        }
    }

    /**
     * Cleanup deleted entities status that older than provided date
     *
     * @param DateTimeImmutable|null $date
     * @param array $salesChannelIds
     * @param Context $context
     * @return void
     */
    public function cleanup(?DateTimeImmutable $date, array $salesChannelIds, Context $context): void
    {
        $criteria = new Criteria();
        $deleteConditions = [];
        if ($date) {
            $deleteConditions[] = new AndFilter([
                new EqualsFilter('state', EntityStatusEntity::STATE_DELETED),
                new RangeFilter('deletedAt', [
                    RangeFilter::LTE => $date->format(Defaults::STORAGE_DATE_TIME_FORMAT)
                ])
            ]);
        }
        if (!empty($salesChannelIds)) {
            $deleteConditions[] = new NotFilter('AND', [
                new EqualsAnyFilter('salesChannelId', $salesChannelIds),
            ]);
        }
        $criteria->addFilter(new OrFilter($deleteConditions));

        $ids = $this->entityStatusRepository->searchIds($criteria, $context)->getIds();

        $removedData = [];
        foreach ($ids as $id) {
            $removedData[] = ['id' => $id];
        }

        foreach(array_chunk($removedData, 100) as $deleteChunk) {
            $this->entityStatusRepository->delete($deleteChunk, $context);
        }
    }

    /**
     * Saves status data
     *
     * @param EntityStatusCollection $entityStatuses
     * @param Context $context
     * @return void
     */
    private function persistEntityStatusCollection(
        EntityStatusCollection $entityStatuses,
        Context $context
    ): void {
        $upsertData = [];
        /** @var EntityStatusEntity $entityStatus */
        foreach ($entityStatuses as $entityStatus) {
            $upsertData[] = [
                'id' => $entityStatus->getId(),
                'entityType' => $entityStatus->getEntityType(),
                'entityId' => Uuid::fromHexToBytes($entityStatus->getEntityId() ?? ''),
                'identifier' => $entityStatus->getIdentifier(),
                'salesChannelId' => $entityStatus->getSalesChannelId(),
                'dataType' => $entityStatus->getDataType(),
                'state' => $entityStatus->getState(),
                'hash' => $entityStatus->getHash(),
                'deletedAt' => $entityStatus->getDeletedAt(),
            ];
        }

        foreach (array_chunk($upsertData, 500) as $chunk) {
            $this->entityStatusRepository->upsert($chunk, $context);
        }
    }
}
