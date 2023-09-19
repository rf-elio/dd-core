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

use Elio\ElioSearch\Core\Sync\Indexer\ChangeSetService;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductEntity;
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
        private readonly LoggerInterface $logger
    ) {
    }

    public function sync(SyncProfileInterface $profile, SyncProfileEntity $syncProfile, SalesChannelContext $context): void
    {
        $changeSet = $this->changeSetService->changeSet($syncProfile->getType(), $syncProfile, $context->getContext());
        if (!$this->hasSyncData($changeSet)) {
            $this->logger->info(sprintf('No entries sync entries found for profile %s', $profile->getName()));
            return;
        }

        $apiWriter = $this->getApiWriter('');
        foreach ($changeSet as $key => $ids) {
            $collection = $this->getCollector($profile->getName())->collect($context, new Criteria($ids));
            foreach ($collection as $entities) {
                if ($key === ChangeSetService::KEY_CREATED) {
                    $apiWriter->create($entities);
                    continue;
                }

                if ($key === ChangeSetService::KEY_UPDATED) {
                    $apiWriter->update($entities);
                    continue;
                }

                if ($key === ChangeSetService::KEY_DELETED) {
                    $apiWriter->delete($entities);
                    continue;
                }

                /** TODO: Change to custom exception */
                throw new \Exception(sprintf('Invalid key %s', $key));
            }
        }
    }

    protected function getCollector(string $name)
    {
        foreach ($this->collectors as $collector) {
            if ($collector->supports($name)) {
                return $collector;
            }
        }

        // thr ex
    }

    protected function getApiWriter(string $name)
    {
        /** @var ApiWriterInterface $apiWriter */
        foreach ($this->apiWriters as $apiWriter) {
            if ($apiWriter->supports($name)) {
                return $apiWriter;
            }
        }

        // thr ex
    }

    private function hasSyncData(array $changeSet): bool
    {
        return !empty($changeSet[ChangeSetService::KEY_CREATED])
            || !empty($changeSet[ChangeSetService::KEY_UPDATED])
            || !empty($changeSet[ChangeSetService::KEY_DELETED]);
    }
}