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
use Elio\ElioSearch\Core\Sync\Collector\DataCollectorInterface;
use Elio\ElioSearch\Core\Sync\Collector\TranslatedEntityCollection;
use Elio\ElioSearch\Core\Sync\DeltaDataCollection;
use Elio\ElioSearch\Core\Sync\Input\Exception\NoSupportedCollectorFoundException;
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
class DeltaInput implements InputInterface
{
    public const TYPE = self::class;

    public function __construct(
        private readonly ChangeSetService $changeSetService,
        private readonly iterable $collectors,
        private readonly LoggerInterface $logger
    )
    {
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
        $changeSet = $this->changeSetService->getChangeSet($syncProfile, $syncContext->getSalesChannelContexts()->getFirst()->getContext());

        if ($changeSet->isEmpty()) {
            $this->logger->info(sprintf('No entries sync entries found for profile %s', $syncContext->getProfileDefinition()->getName()));
            return;
        }

        foreach ($changeSet->getDeleted() as $ids) {
            yield new DeltaDataCollection(DeltaDataCollection::TYPE_DELETED, $ids);
        }

        foreach ($changeSet->getCreated() as $entityType => $ids) {
            foreach ($this->getCollectors($syncProfile->getDataType(), $entityType) as $collector) {
                $collection = $collector->collect($contexts, new Criteria($ids));
                /** @var TranslatedEntityCollection $currentData */
                $currentData = $collection->current() ?? new TranslatedEntityCollection();
                yield new DeltaDataCollection(DeltaDataCollection::TYPE_CREATED, $currentData);
            }
        }

        foreach ($changeSet->getUpdated() as $entityType => $ids) {
            foreach ($this->getCollectors($syncProfile->getDataType(), $entityType) as $collector) {
                $collection = $collector->collect($contexts, new Criteria($ids));
                /** @var TranslatedEntityCollection $currentData */
                $currentData = $collection->current() ?? new TranslatedEntityCollection();
                yield new DeltaDataCollection(DeltaDataCollection::TYPE_UPDATED, $currentData);
            }
        }
    }

    /**
     * Gets profile collectors
     *
     * @param string $dataType
     * @param string $entityType
     * @return DataCollectorInterface[]
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
            throw new NoSupportedCollectorFoundException('Collectors are not found');
        }

        return $collectors;
    }
}