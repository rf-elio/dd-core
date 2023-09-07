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

namespace Elio\ElioSearch\Core\Sync;

use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use InvalidArgumentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class SyncService
{
    public function __construct(private readonly EntityRepository $syncProfileRepository, private readonly iterable $profiles)
    {
    }

    public function syncAll(SyncProfileEntity $syncProfile, Context $context): void
    {
        $profiles = $this->getProfile($syncProfile);

        foreach ($profiles as $profile) {
            if ($profile->getType() === SyncConfig::PROFILE_SYNC) {
                // call sync api service
                continue;
            }

            if ($profile->getType() === SyncConfig::PROFILE_EXPORT) {
                // call export service
                continue;
            }

            throw new InvalidArgumentException(sprintf('Invalid profile type %s', $profile->getType()));
        }
    }

    /**
     * @param Context $context
     * @return EntitySearchResult
     */
    public function getSyncProfiles(Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        return $this->syncProfileRepository->search($criteria, $context);
    }

    protected function getProfile(SyncProfileEntity $syncProfile): SyncProfileInterface
    {
        /** @var SyncProfileInterface $profile */
        foreach ($this->profiles as $profile) {
            if ($profile->getName() === $syncProfile->getProfile()) {
                return $profile;
            }
        }

        throw new InvalidArgumentException('Profile not found');
    }
}