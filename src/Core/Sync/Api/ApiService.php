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

use Elio\ElioSearch\Core\Sync\Api\Event\ApiSyncChangeSetEvent;
use Elio\ElioSearch\Core\Sync\Api\Event\ApiSyncCompleteEvent;
use Elio\ElioSearch\Core\Sync\Api\Exception\ApiSyncException;
use Elio\ElioSearch\Core\Sync\ChangeSet\ChangeSetService;
use Elio\ElioSearch\Core\Sync\Collector\DataCollectorInterface;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ApiService
 * @package Elio\ElioSearch\Core\Sync\Api
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ApiService
{
    public function __construct(
        private readonly ChangeSetService $changeSetService,
        private readonly iterable $apiWriters,
        private readonly iterable $collectors,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Syncs profile data by api
     *
     * @param SyncProfileInterface $profileConfiguration
     * @param SyncProfileEntity $syncProfile
     * @param SalesChannelContext $context
     * @return void
     * @throws Exception
     */
    public function sync(SyncProfileInterface $profileConfiguration, SyncProfileEntity $syncProfile, SalesChannelContext $context): void
    {
        $changeSet = $this->changeSetService->getChangeSet($syncProfile, $context->getContext());
        $this->eventDispatcher->dispatch(new ApiSyncChangeSetEvent($syncProfile, $changeSet, $context));

        if ($changeSet->isEmpty()) {
            $this->logger->info(sprintf('No entries sync entries found for profile %s', $profileConfiguration->getName()));
            return;
        }

        $output = $this->getOutput($syncProfile->getOutput());

        foreach ($changeSet->getDeleted() as $ids) {
            echo '<pre>'; var_dump('DELETE'); die();
            $output->delete($ids, $syncProfile, $context);
        }

        foreach ($changeSet->getCreated() as $entityType => $ids) {
            foreach ($this->getCollectors($syncProfile->getDataType(), $entityType) as $collector) {
                $collection = $collector->collect($syncProfile->getLanguages()->getIds(), $context, new Criteria($ids));
                $currentItems = $collection->current() ?? [];
                $output->create($currentItems, $syncProfile, $context);
            }


            foreach ($collectors as $collector) {
                $collection = $collector->collect($syncProfile->getLanguages()->getIds(), $context, new Criteria($ids));
                echo '<pre>'; var_dump($collection); die();
                $currentItems = $collection->current() ?? [];
                $output->create($currentItems, $syncProfile, $context);
            }
        }

        foreach ($changeSet->getUpdated() as $ids) {
            foreach ($collectors as $collector) {
                $collection = $collector->collect($syncProfile->getLanguages()->getIds(), $context, new Criteria($ids));
                $currentItems = $collection->current() ?? [];
                $output->update($currentItems, $syncProfile, $context);
            }
        }

        $this->eventDispatcher->dispatch(new ApiSyncCompleteEvent($syncProfile, $context));
    }

    /**
     * Gets profile collectors
     *
     * @param string $dataType
     * @param string $entityType
     * @return DataCollectorInterface[]
     * @throws ApiSyncException
     */
    protected function getCollectors(string $dataType, string $entityType): array
    {
        $collectors = [];
        /** @var DataCollectorInterface $collector */
        foreach ($this->collectors as $collector) {
            if ($collector->supports($dataType, $entityType)) {
                $collectors[] = $collector;
            }
        }

        if (empty($collectors)) {
            throw new ApiSyncException('Collectors are not found');
        }

        return $collectors;
    }

    /**
     * Gets profile api writer
     *
     * @param string $name
     * @return OutputInterface
     * @throws ApiSyncException
     */
    protected function getOutput(string $name): OutputInterface
    {
        /** @var OutputInterface $apiWriter */
        foreach ($this->apiWriters as $apiWriter) {
            if ($apiWriter->supports($name)) {
                return $apiWriter;
            }
        }

        throw new ApiSyncException('Api writer is not found');
    }
}