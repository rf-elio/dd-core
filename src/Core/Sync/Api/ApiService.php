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
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ApiService
{
    public function __construct(
        private readonly ChangeSetService $changeSetService,
        private readonly ApiWriterInterface $apiWriter,
        private readonly iterable $converters,
        private readonly iterable $collectors
    )
    {
    }

    public function sync(SyncProfileInterface $profile, SyncProfileEntity $syncProfile, Context $context)
    {
        $this->changeSetService->changeSet('type', $syncProfile, $context);
        $entitiesStatus = $this->changeSetService->getEntitiesStatus('type', $context);
        if (empty($entitiesStatus->getIds())) {
            // thr ex
            return;
        }
        $criteria = new Criteria($entitiesStatus->getIds());
        // TODO: Provide criteria associations

        // TODO: Move it up to not duplicate a code
        $converter = $this->getConverter($profile->getConverter());
        // TODO: Clarify data types
        $collection = $this->getCollector($profile->getDataTypes())->collect($context, $criteria);

        // TODO: Get type

        /** @var ProductEntity $item */
        foreach ($collection as $item) {
            $entityStatus = $entitiesStatus->get($item->getId());
            if ($this->changeSetService->isCreated($entityStatus)) {
                $this->apiWriter->create();
            }

            if ($this->changeSetService->isUpdated($entityStatus)) {
                $this->apiWriter->update();
            }

            if ($this->changeSetService->isDeleted($entityStatus)) {
                $this->apiWriter->delete();
            }

            // thr ex
        }
    }
}