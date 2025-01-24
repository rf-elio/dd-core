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

namespace Elio\ElioDataDiscovery\Core\Sync;

use DateTimeImmutable;
use Elio\ElioDataDiscovery\Core\Sync\Exception\NoSalesChannelDomainsInSyncConfiguredException;
use Elio\ElioDataDiscovery\Core\Sync\Exception\SalesChannelNotFoundException;
use Elio\ElioDataDiscovery\Core\Sync\Exception\SyncProfileNotFoundException;
use Elio\ElioDataDiscovery\Core\Sync\Input\InputService;
use Elio\ElioDataDiscovery\Core\Sync\Output\Message\AsyncOutputHandler;
use Elio\ElioDataDiscovery\Core\Sync\Output\Message\AsyncOutputMessage;
use Elio\ElioDataDiscovery\Core\Sync\Output\OutputService;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class SyncService
 * @package Elio\ElioDataDiscovery\Core\Sync
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class SyncService
{
    public function __construct(
        private readonly EntityRepository $syncProfileRepository,
        private readonly iterable $profileDefinitions,
        private readonly InputService $inputService,
        private readonly OutputService $outputService,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly LoggerInterface $logger,
        private readonly SyncStatusService $syncStatusService,
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * Syncs data for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @param array $options
     * @return void
     * @throws Exception
     */
    public function sync(SyncProfileEntity $syncProfile, array $options = []): void
    {
        $context = Context::createDefaultContext();
        $syncContext = $this->createSyncContext($syncProfile, $options);
        $input = $this->inputService->getInput($syncContext);
        $outputStream = $this->outputService->createOutputStream($syncContext);

        $this->logger->info('Sync: Starting', [
            'id' => $syncProfile->getId(),
            'name' => $syncProfile->getName(),
            'salesChannelDomains' => $syncProfile->getSalesChannelDomains()?->getIds(),
            'profileDefinition' => [
                'name' => $syncContext->getProfileDefinition()->getName(),
                'input' => $syncContext->getProfileDefinition()->getInput(),
                'outputs' => $syncContext->getProfileDefinition()->getOutputs(),
                'features' => $syncContext->getProfileDefinition()->getFeatures(),
                'dataTypes' => $syncContext->getProfileDefinition()->getDataTypes(),
            ],
            'currentProfile' => [
                'input' => $input::class,
                'dataType' => $syncProfile->getDataType(),
            ]
        ]);

        $execution = $this->syncStatusService->createNewSyncProfileExecution($syncProfile, $context);
        $asyncWrite = $syncContext->getProfileDefinition()->getFeatures()[AsyncOutputHandler::SUPPORTS_ASYNC_FEATURE];
        $outputStream->init();
        $outputStream->open();

        $this->logger->info('Sync: Read starting', [
            'id' => $syncProfile->getId(),
            'name' => $syncProfile->getName(),
            'stats' => [
                'asyncWrite' => $asyncWrite ? 'true' : 'false',
            ]
        ]);

        $totalCount = 0;
        foreach ($input->read($syncContext) as $dataCollection) {
            $totalCount++;
            if ($asyncWrite) {
                $this->bus->dispatch(AsyncOutputMessage::create(
                    $syncProfile->getId(),
                    $context,
                    $execution,
                    $dataCollection
                ));
            } else {
                $outputStream->write($dataCollection);
            }
        }

        $this->logger->info('Sync: Read complete', [
            'id' => $syncProfile->getId(),
            'name' => $syncProfile->getName(),
            'stats' => [
                'totalCount' => $totalCount,
                'asyncWrite' => $asyncWrite ? 'true' : 'false',
            ]
        ]);

        $this->syncStatusService->setTotalCount($execution, $totalCount, $context);
        if (!$asyncWrite || $totalCount <= 0) {
            $this->syncStatusService->increaseProcessedCount($execution, $totalCount);
        }
        $this->syncStatusService->checkSyncProfileExecutionStatus($execution, $outputStream, $syncContext, $context);
    }

    public function createSyncContext(SyncProfileEntity $syncProfile, array $options = []): SyncContext
    {
        $salesChannel = $syncProfile->getSalesChannel();
        if (!$salesChannel) {
            $this->logger->info(
                'Cannot generate product sync: no sales channel is configured',
                ['id' => $syncProfile->getId()]
            );
            throw new RuntimeException(sprintf(
                'Cannot generate product sync "%s": no sales channel is configured',
                $syncProfile->getName()
            ));
        }

        if (($syncProfile->getSalesChannelDomains()?->count() ?? 0) <= 0) {
            $this->logger->info(
                'Cannot generate product sync: no sales channel domains configured',
                ['id' => $syncProfile->getId()]
            );
            throw new NoSalesChannelDomainsInSyncConfiguredException(sprintf(
                'No sales channel domains in sync "%s" configured',
                $syncProfile->getName()
            ));
        }

        $salesChannelContexts = new SalesChannelContextCollection();
        foreach ($syncProfile->getSalesChannelDomains() ?? new SalesChannelDomainCollection() as $domain) {
            $salesChannelContexts->add($this->createSalesChannelContext($salesChannel, $domain));
            $salesChannelContexts->addLanguage($domain->getLanguage());
        }

        $profileDefinition = $this->getProfileDefinition($syncProfile);
        return new SyncContext($profileDefinition, $syncProfile, $salesChannelContexts, $options);
    }

    /**
     * @param Context $context
     * @return SalesChannelContext[]
     */
    public function getSalesChannelContexts(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannel');
        $criteria->addAssociation('salesChannelDomains.language');
        $syncProfiles = $this->syncProfileRepository->search($criteria, $context);

        $salesChannelContexts = [];
        /** @var SyncProfileEntity $syncProfile */
        foreach ($syncProfiles as $syncProfile) {
            $salesChannel = $syncProfile->getSalesChannel();
            if (!$salesChannel) {
                throw new SalesChannelNotFoundException(sprintf(
                    'Sales channel for sync profile %s not found',
                    $syncProfile->getId()
                ));
            }

            $salesChannelContexts[$salesChannel->getId()] = $this->createSalesChannelContext(
                $salesChannel,
                $syncProfile->getSalesChannelDomains()?->first()
            );
        }

        return $salesChannelContexts;
    }

    private function createSalesChannelContext(SalesChannelEntity $salesChannel, SalesChannelDomainEntity $domain): SalesChannelContext
    {
        return $this->salesChannelContextFactory->create(
            '',
            $salesChannel->getId(),
            [SalesChannelContextService::LANGUAGE_ID => $domain->getLanguageId()]
        );
    }

    /**
     * Gets all active profiles
     *
     * @param Context $context
     * @return EntitySearchResult
     */
    public function getSyncProfileConfigurations(Context $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannelDomains.language.locale');
        $criteria->addAssociation('salesChannel');
        $criteria->addFilter(new EqualsFilter('active', true));
        return $this->syncProfileRepository->search($criteria, $context);
    }

    /**
     * Get sync profile by id
     *
     * @param string $id
     * @param Context $context
     * @return SyncProfileEntity
     */
    public function getSyncProfileConfiguration(string $id, Context $context): SyncProfileEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('salesChannelDomains.language.locale');
        $criteria->addAssociation('salesChannel');
        $criteria->addFilter(new EqualsFilter('active', true));
        if (!$syncProfile = $this->syncProfileRepository->search($criteria, $context)->first()) {
            throw new SyncProfileNotFoundException(sprintf('Sync profile for id %s not found', $id));
        }

        return $syncProfile;
    }

    /**
     * Searches all due sync profiles
     *
     * @param Context $context
     * @return SyncProfileCollection
     */
    public function getDueSyncProfileConfigurations(Context $context): SyncProfileCollection
    {
        $syncProfiles = $this->getSyncProfileConfigurations($context);
        $dueSyncProfiles = new SyncProfileCollection();
        /** @var SyncProfileEntity $syncProfile */
        foreach ($syncProfiles as $syncProfile) {
            if ($this->isSyncProfileDue($syncProfile)) {
                $dueSyncProfiles->add($syncProfile);
            }
        }

        return $dueSyncProfiles;
    }

    public function isSyncProfileDue(SyncProfileEntity $syncProfile): bool
    {
        $now = new DateTimeImmutable();
        return !$syncProfile->getNextGenerationDueAt() || $syncProfile->getNextGenerationDueAt() <= $now;
    }

    /**
     * Gets profile configuration
     *
     * @param SyncProfileEntity $syncProfile
     * @return ProfileInterface
     */
    protected function getProfileDefinition(SyncProfileEntity $syncProfile): ProfileInterface
    {
        /** @var ProfileInterface $profileDefinition */
        foreach ($this->profileDefinitions as $profileDefinition) {
            if ($profileDefinition->getName() === $syncProfile->getProfile()) {
                return $profileDefinition;
            }
        }

        throw new InvalidArgumentException('Profile not found');
    }
}
