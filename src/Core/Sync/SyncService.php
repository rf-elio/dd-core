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
use Elio\ElioDataDiscovery\Core\Sync\Event\SyncGeneratedEvent;
use Elio\ElioDataDiscovery\Core\Sync\Exception\NoLanguagesInSyncConfiguredException;
use Elio\ElioDataDiscovery\Core\Sync\Input\InputService;
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
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Psr\EventDispatcher\EventDispatcherInterface;

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
        private readonly EntityRepository                   $syncProfileRepository,
        private readonly iterable                           $profileDefinitions,
        private readonly InputService                       $inputService,
        private readonly OutputService                      $outputService,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly LoggerInterface                    $logger,
        private readonly EventDispatcherInterface           $eventDispatcher,
    )
    {
    }

    /**
     * Syncs data for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @return void
     * @throws Exception
     */
    public function sync(SyncProfileEntity $syncProfile): void
    {
        $context = Context::createDefaultContext();
        $salesChannel = $syncProfile->getSalesChannel();
        if (!$salesChannel || !$salesChannel->getDomains()) {
            $this->logger->info(
                'Cannot generate product sync: association "salesChannel.domains" is missing',
                ['id' => $syncProfile->getId()]
            );

            throw new RuntimeException(sprintf(
                'Cannot generate product sync "%s": association "salesChannel.domains" is missing',
                $syncProfile->getName()
            ));
        }

        if (($syncProfile->getLanguages()?->count() ?? 0) <= 0) {
            throw new NoLanguagesInSyncConfiguredException(sprintf(
                'No languages in sync "%s" configured',
                $syncProfile->getName()
            ));
        }

        $salesChannelContexts = new SalesChannelContextCollection();
        foreach ($syncProfile->getLanguages() ?? new LanguageCollection() as $language) {
            $salesChannelContexts->add($this->salesChannelContextFactory->create(
                '',
                $salesChannel->getId(),
                [SalesChannelContextService::LANGUAGE_ID => $language->getId()]
            ));
            $salesChannelContexts->addLanguage($language);
        }

        $profileDefinition = $this->getProfileDefinition($syncProfile);
        $syncContext = new SyncContext($profileDefinition, $syncProfile, $salesChannelContexts);
        $input = $this->inputService->getInput($syncContext);
        $outputStream = $this->outputService->createOutputStream($syncContext);

        $this->logger->info('Sync: Starting', [
            'id' => $syncProfile->getId(),
            'name' => $syncProfile->getName(),
            'languages' => $syncProfile->getLanguages()?->getIds(),
            'profileDefinition' => [
                'name' => $profileDefinition->getName(),
                'input' => $profileDefinition->getInput(),
                'outputs' => $profileDefinition->getOutputs(),
                'features' => $profileDefinition->getFeatures(),
                'dataTypes' => $profileDefinition->getDataTypes(),
            ],
            'currentProfile' => [
                'input' => $input::class,
                'dataType' => $syncProfile->getDataType(),
            ]
        ]);

        $this->setStartDate($syncProfile, $context);
        $outputStream->open();

        foreach ($input->read($syncContext) as $dataCollection) {
            $outputStream->write($dataCollection);
        }

        $outputStream->close();
        $this->eventDispatcher->dispatch(new SyncGeneratedEvent($syncProfile, $syncContext->getSalesChannelContexts()));
        $this->setFinishDate($syncProfile, $context);
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
        $criteria->addAssociation('salesChannel.domains');
        $criteria->addAssociation('languages.locale');
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
        $criteria->addAssociation('salesChannel.domains');
        $criteria->addAssociation('languages.locale');
        $criteria->addFilter(new EqualsFilter('active', true));
        if (!$syncProfile = $this->syncProfileRepository->search($criteria, $context)->first()) {
            throw new InvalidArgumentException(sprintf('Sync profile for id %s not found', $id));
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
        $now = new DateTimeImmutable();
        $syncProfiles = $this->getSyncProfileConfigurations($context);
        $dueSyncProfiles = new SyncProfileCollection();
        /** @var SyncProfileEntity $syncProfile */
        foreach ($syncProfiles as $syncProfile) {
            if (!$syncProfile->getNextGenerationDueAt() || $syncProfile->getNextGenerationDueAt() <= $now) {
                $dueSyncProfiles->add($syncProfile);
            }
        }

        return $dueSyncProfiles;
    }

    /**
     * Changes generation started date for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @return void
     */
    protected function setStartDate(SyncProfileEntity $syncProfile, Context $context): void
    {
        $this->syncProfileRepository->update([
            [
                'id' => $syncProfile->getId(),
                'lastGenerationStartedAt' => new DateTimeImmutable(),
            ]
        ], $context);
    }

    /**
     * Changes generation finished date for profile
     *
     * @param SyncProfileEntity $syncProfile
     * @param Context $context
     * @return void
     */
    protected function setFinishDate(SyncProfileEntity $syncProfile, Context $context): void
    {
        $this->syncProfileRepository->update([
            [
                'id' => $syncProfile->getId(),
                'lastGenerationFinishedAt' => new DateTimeImmutable(),
            ]
        ], $context);
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
