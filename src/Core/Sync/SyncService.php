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

use DateTimeImmutable;
use Elio\ElioSearch\Core\Sync\Api\ApiService;
use Elio\ElioSearch\Core\Sync\Export\ExportService;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;

class SyncService
{
    public function __construct(
        private readonly ExportService $exportService,
        private readonly ApiService $apiService,
        private readonly EntityRepository $syncProfileRepository,
        private readonly iterable $profiles,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly LoggerInterface $logger,
        private readonly EntityRepository $syncProfileRepository1,
    ) {
    }

    public function syncAll(SyncProfileEntity $syncProfile): void
    {
        $salesChannel = $syncProfile->getSalesChannel();
        if(!$salesChannel || !$salesChannel->getDomains()) {
            $this->logger->info(
                'Cannot generate product export: association "salesChannel.domains" is missing',
                ['id' => $syncProfile->getId()]
            );

            throw new RuntimeException(sprintf(
                'Cannot generate product export "%s": association "salesChannel.domains" is missing',
                $syncProfile->getName()
            ));
        }

        $languageId = $syncProfile->getLanguages()?->first()?->getId();
        $salesChannelContext = $this->salesChannelContextFactory->create('', $salesChannel->getId(), [SalesChannelContextService::LANGUAGE_ID => $languageId]);

        $this->startSync($syncProfile, $salesChannelContext->getContext());
        $profile = $this->getProfile($syncProfile);
        if ($syncProfile->getType() === SyncConfig::PROFILE_SYNC) {
            $this->apiService->sync($profile, $syncProfile, $salesChannelContext);
            return;
        }

        if ($syncProfile->getType() === SyncConfig::PROFILE_EXPORT) {
            $this->exportService->export($profile, $syncProfile, $salesChannelContext);
            return;
        }

        throw new InvalidArgumentException(sprintf('Invalid profile type %s', $syncProfile->getType()));
    }

    /**
     * @param Context $context
     * @return EntitySearchResult
     */
    public function getSyncProfiles(Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannel.domains');
        $criteria->addFilter(new EqualsFilter('active', true));
        return $this->syncProfileRepository->search($criteria, $context);
    }

    protected function startSync(SyncProfileEntity $syncProfile, Context $context): void
    {
        $this->syncProfileRepository->update([[
            'id' => $syncProfile->getId(),
            'lastGenerationStartedAt' => new DateTimeImmutable()
        ]], $context);
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