<?php
/**
 * Copyright (c) 2020, elio GmbH.
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

use Elio\FactFinder\Core\Export\ExportService;
use Exception;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportGenerateCommand
 *
 * @category  Shopware
 * @package   Shopware\Plugins\FactFinder\Command
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class ExportGenerateCommand extends Command
{
    private ExportService $exportService;

    /**
     * ExportGenerateCommand constructor.
     * @param ExportService $exportService
     */
    public function __construct(ExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    protected function configure(): void
    {
        $this->setName('elio-ff:export:generate')
             ->addArgument('exportId', InputArgument::OPTIONAL, 'Id of the export that should be generated')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignores the schedule');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        $exportId = $input->getArgument('exportId');
        $context = new Context(new SystemSource());

        $consoleMessage = 'Loading all exports';
        $criteria = new Criteria();
        if($exportId) {
            $criteria->addFilter(new EqualsFilter('id', $exportId));
            $consoleMessage = 'Loading export "'.$exportId.'"';
        }

        if($force) {
            $consoleMessage .= ' ignoring due';
            $dueExports = $this->exportService->getExports($criteria, $context);
        } else {
            $consoleMessage .= ' only due';
            $dueExports = $this->exportService->getDueExports($criteria, $context);
        }

        $output->writeln('<info>'.$consoleMessage.'</info>');

        if($dueExports->count() <= 0) {
            $output->writeln('<comment>No exports to execute found</comment>');
        }

        foreach ($dueExports as $dueExport) {
            $output->writeln(sprintf(
                '<info>Generating export: "%s" with id "%s"</info>', $dueExport->getName(), $dueExport->getId()
            ));
            $this->exportService->generate($dueExport, $context);
        }

        return Command::SUCCESS;
    }
}
