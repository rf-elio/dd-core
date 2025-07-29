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

namespace Elio\ElioDataDiscovery\Command;

use Elio\ElioDataDiscovery\Core\Sync\SyncProfileEntity;
use Elio\ElioDataDiscovery\Core\Sync\SyncService;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SyncDataCommand
 * @package Elio\ElioDataDiscovery\Command
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class SyncDataCommand extends Command
{
    public function __construct(
        private readonly SyncService $syncService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('elio-data-discovery:profiles:sync')
            ->addArgument('syncProfileId', InputArgument::OPTIONAL, 'Id of the sync profile that should be generated')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignores the schedule')
            ->addOption('full-sync', 'F', InputOption::VALUE_NONE, 'Executed a full sync of all data');
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
        $exportId = $input->getArgument('syncProfileId');
        $force = $input->getOption('force');

        try {
            if($exportId) {
                $syncProfileConfiguration = $this->syncService->getSyncProfileConfiguration($exportId, $context);
                if (!$force && !$this->syncService->isSyncProfileDue($syncProfileConfiguration)) {
                    $output->writeln('<info>Sync profile is not due</info>');
                    return Command::SUCCESS;
                }

                $this->syncService->sync($syncProfileConfiguration, $input->getOptions());
                return Command::SUCCESS;
            }

            if($force) {
                $syncProfileConfigurations = $this->syncService->getSyncProfileConfigurations($context);
            } else {
                $syncProfileConfigurations = $this->syncService->getDueSyncProfileConfigurations($context);
            }

            if ($syncProfileConfigurations->count() <= 0) {
                $output->writeln('<info>No due sync profiles found</info>');
                return Command::SUCCESS;
            }

            /** @var SyncProfileEntity $syncProfileConfiguration */
            foreach ($syncProfileConfigurations as $syncProfileConfiguration) {
                $this->syncService->sync($syncProfileConfiguration, $input->getOptions());
            }
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
}
