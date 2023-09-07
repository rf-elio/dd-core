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

namespace Elio\ElioSearch\Core\Sync\Indexer;

use Elio\ElioSearch\Core\Sync\Api\EntityStatusCollection;
use Elio\ElioSearch\Core\Sync\Api\EntityStatusEntity;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ChangeSetService
{
    private array $entitiesState = [];
    private string $type;
    private array $created = [];
    private array $updated = [];
    private array $deleted = [];

    public function __construct(private readonly EntityRepository $entityStatusRepository)
    {
    }

    public function changeSet(string $type, SyncProfileEntity $syncProfile, Context $context)
    {
        $indexer = $this->getIndexer($type);
        $entitiesStatus = $this->getEntitiesStatus($type, $context);
        $changed = $indexer->index($syncProfile, $context, $entitiesStatus);

        foreach (array_chunk($changed, 500) as $chunk) {
            $this->entityStatusRepository->upsert($chunk, $context);
        }
    }

    public function getEntitiesStatus(string $type, Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('deletedAt', null)); // or deletedAt < $now->modify(+1 day). could be moved to config
        $criteria->addFilter(new EqualsFilter('type', $type));
        return $this->entityStatusRepository->search($criteria, $context);

    }

    public function isCreated(EntityStatusEntity $entityStatus): bool
    {
        // updatedAt and deletedAt are null
    }

    public function isUpdated(EntityStatusEntity $entityStatus): bool
    {
        // updatedAt < $now->modify(+1 day)
    }

    public function isDeleted(EntityStatusEntity $entityStatus): bool
    {
        // deletedAt < $now->modify(+1 day)
    }

    protected function getIndexer(string $type)
    {
        // iterate an indexer
    }
}