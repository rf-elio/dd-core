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

namespace Elio\FactFinder\Core\RealTimeUpdate;

use Elio\FactFinder\Api\Import\ImportApi;
use Elio\FactFinder\Api\Import\Request\SearchImportRequest;
use Elio\FactFinder\Api\Import\Request\SuggestImportRequest;
use Elio\FactFinder\Api\Import\Response\ImportResponse;
use Elio\FactFinder\Configuration\FactFinderConfigService;
use Elio\FactFinder\Core\Export\ExportConfig;
use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\Generator\Content\ContentExportDefaults;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class ImportService
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ImportService implements ImportServiceInterface
{
    private FactFinderConfigService $configService;
    private ImportApi $importApi;
    private LoggerInterface $logger;

    /**
     * ImportService constructor.
     * @param FactFinderConfigService $configService
     * @param ImportApi $importApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        FactFinderConfigService $configService,
        ImportApi $importApi,
        LoggerInterface $logger
    )
    {
        $this->configService = $configService;
        $this->importApi = $importApi;
        $this->logger = $logger;
    }

    /**
     * Starts the import at ff via the ff api
     *
     * @param ExportEntity $export
     * @param SalesChannelContext $salesChannelContext
     * @return ImportResponse[]
     */
    public function import(ExportEntity $export, SalesChannelContext $salesChannelContext): array
    {
        $config = $this->configService->getByContext($salesChannelContext);
        $results = [];
        $exportConfig = $export->getConfig();

        try {
            if ($exportConfig[ExportConfig::TRIGGER_IMPORT_SEARCH_DATA] ?? false) {
                $searchImportChannel = $export->getType() === ContentExportDefaults::TYPE ?
                    $config->getApiContentChannel() : $config->getApiChannel();

                $importRequest = new SearchImportRequest($searchImportChannel);
                $responseCollection = $this->importApi->searchImport($importRequest, $salesChannelContext);
                if ($importResponse = $responseCollection->get(ImportResponse::class)) {
                    $results[] = $importResponse;
                }
            }

            if ($exportConfig[ExportConfig::TRIGGER_IMPORT_SUGGEST_DATA] ?? false) {
                $importRequest = new SuggestImportRequest($config->getApiChannel());
                $responseCollection = $this->importApi->suggestImport($importRequest, $salesChannelContext);
                if ($importResponse = $responseCollection->get(ImportResponse::class)) {
                    $results[] = $importResponse;
                }
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $results;
    }
}