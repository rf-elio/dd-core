<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Sync;


use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Elio\ElioDataDiscovery\Core\Sync\Event\SyncGeneratedEvent;
use Elio\ElioDataDiscovery\Core\Sync\Exception\SyncProfileExecutionNotActiveException;
use Elio\ElioDataDiscovery\Core\Sync\Output\OutputStream;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Cron\CronExpression;
use Cron\FieldFactory;

/**
 * Class SyncStatusService
 * @package Elio\ElioDataDiscovery\Core\Sync
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class SyncStatusService
{
    public function __construct(
        private readonly EntityRepository $syncProfileRepository,
        private readonly EntityRepository $syncProfileExecutionRepository,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Creates new sync profile execution record and returns it
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @return SyncProfileExecutionEntity
     */
    public function createNewSyncProfileExecution(
        SyncProfileEntity $syncProfile,
        Context $context
    ): SyncProfileExecutionEntity {
        $this->cleanup($syncProfile, $context);

        $syncProfileExecutionId = Uuid::randomHex();
        $this->syncProfileExecutionRepository->create([
            [
                'id' => $syncProfileExecutionId,
                'syncProfileId' => $syncProfile->getId()
            ]
        ], $context);

        return $this->getSyncProfileExecutionById($syncProfileExecutionId, $context);
    }

    private function cleanup(
        SyncProfileEntity $syncProfile,
        Context $context
    ): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('syncProfileId', $syncProfile->getId()));
        $oldIds = $this->syncProfileExecutionRepository->searchIds($criteria, $context)->getIds();
        $oldIds = array_map(fn($id) => ['id' => $id], $oldIds);
        $this->syncProfileExecutionRepository->delete($oldIds, $context);
    }

    public function getSyncProfileExecutionById(string $id, Context $context): SyncProfileExecutionEntity
    {
        $criteria = new Criteria([$id]);
        $syncProfileExecution = $this->syncProfileExecutionRepository->search($criteria, $context)->first();

        if (!$syncProfileExecution) {
            throw new SyncProfileExecutionNotActiveException($id);
        }

        return $syncProfileExecution;
    }

    /**
     * Sets total count of data chunks for sync profile execution record
     *
     * @param SyncProfileExecutionEntity $syncProfileExecutionEntity
     * @param int $totalCount
     * @param Context $context
     * @return void
     */
    public function setTotalCount(
        SyncProfileExecutionEntity $syncProfileExecutionEntity,
        int $totalCount,
        Context $context
    ): void {
        $this->syncProfileExecutionRepository->update([
            [
                'id' => $syncProfileExecutionEntity->getId(),
                'totalCount' => $totalCount,
            ]
        ], $context);
    }

    /**
     * @param SyncProfileExecutionEntity $syncProfileExecutionEntity
     * @param int $processedCount
     * @return void
     * @throws Exception
     */
    public function increaseProcessedCount(
        SyncProfileExecutionEntity $syncProfileExecutionEntity,
        int $processedCount = 1
    ): void {
        $this->connection->executeQuery('
            UPDATE `elio_data_discovery_sync_profile_execution`
            SET `processed_count` = IFNULL(`processed_count`, 0) + :processedCount
            WHERE id = :id',
            [
                'id' => Uuid::fromHexToBytes($syncProfileExecutionEntity->getId()),
                'processedCount' => $processedCount,
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function checkSyncProfileExecutionStatus(
        SyncProfileExecutionEntity $syncProfileExecutionEntity,
        OutputStream $outputStream,
        SyncContext $syncContext,
        Context $context
    ): void
    {
        $syncProfileExecution = $this->getSyncProfileExecutionById($syncProfileExecutionEntity->getId(), $context);
        $totalCount = $syncProfileExecution->getTotalCount();
        $processedCount = $syncProfileExecution->getProcessedCount();

        // sync not completed yet
        if ($totalCount === null || $totalCount !== $processedCount) {
            $this->logger->debug('SyncStatus: Sync incomplete', [
                'id' => $syncProfileExecutionEntity->getSyncProfileId(),
                'total' => $totalCount,
                'processed' => $processedCount,
            ]);
            return;
        }

        $this->logger->info('SyncStatus: Sync complete', [
            'id' => $syncProfileExecutionEntity->getSyncProfileId()
        ]);
        $outputStream->close();
        $this->eventDispatcher->dispatch(new SyncGeneratedEvent(
            $syncContext->getSyncProfile(), $syncContext->getSalesChannelContexts()
        ));
        $this->setStartDate($syncContext->getSyncProfile(), $syncProfileExecutionEntity->getCreatedAt(), $context);
        $this->setFinishDate($syncContext->getSyncProfile(), $context);
    }


    /**
     * Changes generation started date for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @param DateTimeInterface $startDate
     * @param Context $context
     * @return void
     */
    private function setStartDate(SyncProfileEntity $syncProfile, DateTimeInterface $startDate, Context $context): void
    {
        $this->syncProfileRepository->update([
            [
                'id' => $syncProfile->getId(),
                'lastGenerationStartedAt' => $startDate,
            ]
        ], $context);
    }

    /**
     * Changes generation finished date for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @return void
     * @throws \Exception
     */
    protected function setFinishDate(SyncProfileEntity $syncProfile, Context $context): void
    {
        $cron = new CronExpression($syncProfile->getInterval(), new FieldFactory());
        $this->syncProfileRepository->update([
            [
                'id' => $syncProfile->getId(),
                'lastGenerationFinishedAt' => new DateTimeImmutable(),
                'nextGenerationDueAt' => $cron->getNextRunDate()->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]
        ], $context);
    }
}