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


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\RecordRequest;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Export\ExportService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Swagger\Client\ApiException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RecordsGetCommand
 * @package Elio\FactFinder\Command
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class RecordsGetCommand extends Command
{
    private RecordsApi $recordsApi;
    private FactFinderConfigServiceInterface $configService;
    private EntityRepositoryInterface $salesChannelRepository;

    /**
     * ExportGenerateCommand constructor.
     * @param RecordsApi $recordsApi
     * @param FactFinderConfigServiceInterface $configService
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct(
        RecordsApi $recordsApi,
        FactFinderConfigServiceInterface $configService,
        EntityRepositoryInterface $salesChannelRepository
    )
    {
        parent::__construct();
        $this->recordsApi = $recordsApi;
        $this->configService = $configService;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    protected function configure(): void
    {
        $this->setName('elio-ff:record:get')
            ->addArgument('id', InputArgument::REQUIRED, 'Record that should be fetched. The product id must be provided.')
            ->addArgument('languageId', InputArgument::OPTIONAL, 'LanguageId to get language-based api configuration. Optional, if not set global settings will be fetched.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ApiException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $languageId = $input->getArgument('languageId');
        $context = Context::createDefaultContext();
        $salesChannels = $this->salesChannelRepository->search(new Criteria(), $context);

        $table = new Table($output);
        $table->setHeaders(['Channel', 'Record']);

        if($languageId) {
            $this->configService->setLanguagePrefix($languageId);
        }

        /** @var SalesChannelEntity $salesChannel */
        foreach ($salesChannels as $salesChannel) {
            $config = $this->configService->get($salesChannel->getId());

            if(!$config->isActive()) {
                continue;
            }

            $request = new RecordRequest($config->getApiChannel(), $id);
            $records = $this->recordsApi->getRecords($request, $salesChannel->getId());

            foreach ($records->getRecords() as $record) {
                $table->addRow([
                    $salesChannel->getName(),
                    json_encode($record->getMasterValues())
                ]);
            }
        }

        $table->render();
        return Command::SUCCESS;
    }
}
