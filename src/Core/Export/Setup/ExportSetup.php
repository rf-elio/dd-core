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

namespace Elio\FactFinder\Core\Export\Setup;

use Elio\FactFinder\Core\Export\Generator\ProductExportGenerator;
use Elio\FactFinder\Core\Export\Generator\SuggestExportGenerator;
use Elio\FactFinder\Core\Export\Writer\CSVFileWriter;
use Elio\FactFinder\Core\Export\Writer\XMLFileWriter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExportSetup
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ExportSetup
{
    private ?EntityRepository $exportRepository;
    private ?EntityRepository $salesChannelRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->exportRepository = $container->get('elio_ff_export.repository');
        $this->salesChannelRepository = $container->get('sales_channel.repository');
    }

    /**
     * @param Context $context
     * @param array|null $types
     * @param array|null $formats
     */
    public function createExports(Context $context, ?array $types = null, ?array $formats = null)
    {
        $exportTypes = $types ?? [ProductExportGenerator::TYPE, SuggestExportGenerator::TYPE];
        $exportFormats = $formats ?? [CSVFileWriter::TYPE, XMLFileWriter::TYPE];
        $salesChannels = $this->salesChannelRepository->search(new Criteria(), $context)->getEntities();
        $exports = [];
        /** @var SalesChannelEntity $salesChannel */
        foreach ($salesChannels as $salesChannel){
            foreach ($exportTypes as $exportType){
                foreach ($exportFormats as $exportFormat){
                    $criteria = new Criteria();
                    $criteria->addFilter(new EqualsFilter('type', $exportType));
                    $criteria->addFilter(new EqualsFilter('format', $exportFormat));
                    $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));
                    $criteria->addAssociation('salesChannel.domains');
                    if ($this->exportRepository->searchIds($criteria, $context)->getTotal() > 0){
                        continue;
                    }
                    $exports[] = [
                        'id' => Uuid::randomHex(),
                        'name' => $salesChannel->getName().'_'.$exportType.'_'.$exportFormat,
                        'active' => true,
                        'type' => $exportType,
                        'format' => $exportFormat,
                        'interval' => '0 * * * *',
                        'salesChannelId' => $salesChannel->getId(),
                    ];
                }
            }
        }
        if (!empty($exports)){
            $this->exportRepository->create($exports, $context);
        }
    }
}