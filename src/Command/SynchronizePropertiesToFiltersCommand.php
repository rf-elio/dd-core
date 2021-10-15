<?php
/**
 * Copyright (c) 2021, elio GmbH.
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

namespace Elio\FactFinder\Command;

use Elio\FactFinder\Core\FilterRestrictions\FilterService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class SynchronizePropertiesToFiltersCommand
 *
 * @category  Shopware
 * @package   Shopware\Plugins\FactFinder\Command
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (http://www.elio-systems.com)
 */
class SynchronizePropertiesToFiltersCommand extends Command
{
    private FilterService $filterService;

    /**
     * SynchronizePropertiesToFiltersCommand constructor.
     */
    public function __construct(FilterService $filterService)
    {
        parent::__construct();
        $this->filterService = $filterService;
    }

    protected function configure(): void
    {
        $this->setName('elio-ff:filters:sync')
            ->addArgument(
                'propertyId',
                InputArgument::OPTIONAL,
                'Property id that should be synced. Optional. If not provided all properties will be synchronized.'
            );
    }

    /**
     * Starts the sync process that updates the filter list by the shopware properties
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $propertyId = $input->getArgument('propertyId');
        $context = Context::createDefaultContext();
        try {
            if ($propertyId) {
                $this->syncOne($context, $output, $propertyId);
            } else {
                $this->syncAll($context, $output);
            }
        } catch (Throwable $e) {
            return Command::FAILURE;
        }
        $output->writeln('<info>Success</info>');
        return Command::SUCCESS;
    }

    /**
     * Synchronizing property name to filter name by provided propertyId
     *
     * @param Context $context
     * @param OutputInterface $output
     * @param string $propertyId
     */
    private function syncOne(Context $context, OutputInterface $output, string $propertyId)
    {
        $output->writeln(sprintf('<info>Sync property with id : "%s"</info>', $propertyId));
        $this->filterService->syncOne($context, $propertyId);
    }

    /**
     * Synchronizing properties name to filter names for all properties
     *
     * @param Context $context
     * @param OutputInterface $output
     */
    private function syncAll(Context $context, OutputInterface $output)
    {
        $output->writeln('<info>PropertyId is not defined, sync all...</info>');
        $this->filterService->syncAll($context);
    }
}