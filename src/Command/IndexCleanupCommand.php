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

namespace Elio\ElioSearch\Command;

use DateTimeImmutable;
use Elio\ElioSearch\Core\Sync\ChangeSet\ChangeSetService;
use Elio\ElioSearch\Core\Sync\SyncProfileCollection;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Elio\ElioSearch\Core\Sync\SyncService;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IndexCleanupCommand
 * @package Elio\ElioSearch\Command
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class IndexCleanupCommand extends Command
{
    public function __construct(
        private readonly SyncService $syncService,
        private readonly ChangeSetService $changeSetService,
        private readonly SystemConfigService $configService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('elio-search:index:cleanup');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        /** @var SyncProfileCollection $syncProfiles */
        $syncProfiles = $this->syncService->getSyncProfileConfigurations($context)->getEntities();

        if ($this->hasNotExecutedSyncProfiles($syncProfiles)) {
            $output->writeln('<error>Cannot cleanup because, sync profile(s) exists that have not been executed yet.</error>');
            return Command::FAILURE;
        }

        $daysBeforeCleanup = $this->configService->get('entityStatusMaxCleanupAgeInDays') ?? 14;
        $sortedProfile = $this->getLeastRecentlyFinishedSyncProfile($syncProfiles);
        $cleanupDate = (new DateTimeImmutable($sortedProfile->getLastGenerationFinishedAt()
            ?->format(Defaults::STORAGE_DATE_TIME_FORMAT)))
            ->modify('-' . $daysBeforeCleanup . 'day');

        try {
            $this->changeSetService->cleanup($cleanupDate, $context);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ]);
            $output->writeln('<error>'.$e->getMessage().'</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Checks if there are any sync profiles for that we don't have an execution yet.
     *
     * @param SyncProfileCollection $syncProfiles
     * @return bool
     */
    private function hasNotExecutedSyncProfiles(SyncProfileCollection $syncProfiles): bool
    {
        return $syncProfiles->filter(fn(SyncProfileEntity $syncProfile) => $syncProfile->getLastGenerationFinishedAt() === null)->count() > 0;
    }

    /**
     * Searches for the sync profile with the oldest generation finished at date.
     *
     * @param SyncProfileCollection $syncProfiles
     * @return SyncProfileEntity
     */
    private function getLeastRecentlyFinishedSyncProfile(SyncProfileCollection $syncProfiles): SyncProfileEntity
    {
        $syncProfiles->sort(fn(SyncProfileEntity $syncProfile) => $syncProfile->getLastGenerationFinishedAt()?->getTimestamp());
        return $syncProfiles->first();
    }
}