<?php declare(strict_types=1);

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

namespace Elio\FactFinder\Service\Export;

use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Content\ProductExport\Struct\ExportBehavior;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;


/**
 * Manage the fact-finder product export. It can install and generate product export
 *
 * Class ExportManager
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service\Export
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class ExportManager implements ExportManagerInterface
{
    const TEMPLATE_BODY = "body_template.txt";
    const TEMPLATE_HEADER = "header_template.txt";
    const  PRODUCT_STREAM_ID = '99d0a8005d544239b2ca848a629d36ea';

    /**
     * @var EntityRepositoryInterface
     */
    private $productExportRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelDomainRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $productStreamRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SalesChannelEntity[]
     */
    private $salesChannels;

    /**
     * @var ExporterInterface
     */
    private $exporter;

    /**
     * @param EntityRepositoryInterface $productExportRepository
     * @param EntityRepositoryInterface $saleschannelDomainRepository
     * @param EntityRepositoryInterface $productStreamRepository
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param ExporterInterface $exporter
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function __construct
    (
        EntityRepositoryInterface $productExportRepository,
        EntityRepositoryInterface $saleschannelDomainRepository,
        EntityRepositoryInterface $productStreamRepository,
        EntityRepositoryInterface $salesChannelRepository,
        ExporterInterface $exporter
    )
    {
        $this->productExportRepository = $productExportRepository;
        $this->salesChannelDomainRepository = $saleschannelDomainRepository;
        $this->productStreamRepository = $productStreamRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->exporter = $exporter;

        $this->context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->addAssociation('language');
        $this->salesChannels =  $this->salesChannelRepository->search($criteria, $this->context)->getElements();
    }


    public function install():bool
    {
        foreach ($this->salesChannels as $salesChannel) {
            $language = $salesChannel->getLanguage()->getName();
            $filename = "factfinder_".strtolower($salesChannel->getName())."_".strtolower($language).".csv";
            $this->createEntity($filename, $salesChannel);
        }
        return true;
    }

    /**
     * @param string $filename
     * @param SalesChannelEntity $salesChannel
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    private function createEntity(string $filename, SalesChannelEntity $salesChannel): void
    {
        $dir = dirname(__FILE__)."/"."ExportTemplate"."/";
        if(!$templateHeader = file_get_contents($dir.self::TEMPLATE_HEADER))
            throw new \Exception("Couldn't read header template file: ".self::TEMPLATE_HEADER);

        if(!$templateBody = file_get_contents($dir.self::TEMPLATE_BODY))
            throw new \Exception("Couldn't read body template file: ".self::TEMPLATE_BODY);

        $this->productStreamRepository->upsert([
            [
                'id' => self::PRODUCT_STREAM_ID,
                'name' => 'Fact-finder product stream',
                'description' =>'Automatic created product stream to get all active products during product export.',
                'filters' => [["type"=> "multi", "queries"=> [["type"=> "multi",
                "queries" => [["type"=> "equals", "field"=> "product.active",
                "value" => "1"]], "operator"=> "AND"]], "operator"=> "OR"]]
            ],
        ], $this->context);

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('fileName', $filename)
        );

        /** @var ProductExportEntity[] $productExports */
        $productExport = $this->productExportRepository->search($criteria, $this->context)->first();

        if(empty($productExport)){
            $this->productExportRepository->upsert([
                [
                    'fileName' => $filename,
                    'accessKey' => $salesChannel->getAccessKey(),
                    'encoding' => ProductExportEntity::ENCODING_UTF8,
                    'fileFormat' => ProductExportEntity::FILE_FORMAT_CSV,
                    'interval' => 0,
                    'headerTemplate' => $templateHeader,
                    'bodyTemplate' => $templateBody,
                    'productStreamId' => self::PRODUCT_STREAM_ID,
                    'storefrontSalesChannelId' => $this->getSalesChannelDomain()->getSalesChannelId(),
                    'salesChannelId' => $salesChannel->getId(),
                    'salesChannelDomainId' => $this->getSalesChannelDomain()->getId(),
                    'generateByCronjob' => false,
                    'currencyId' => Defaults::CURRENCY,
                ],
            ], $this->context);
        }else{
            $this->productExportRepository->update([
                [
                    'id'=> $productExport->getId(),
                    'accessKey' => $salesChannel->getAccessKey(),
                    'interval' => 0,
                    'headerTemplate' => $templateHeader,
                    'bodyTemplate' => $templateBody,
                    'productStreamId' => self::PRODUCT_STREAM_ID,
                    'storefrontSalesChannelId' => $this->getSalesChannelDomain()->getSalesChannelId(),
                    'salesChannelId' => $salesChannel->getId(),
                    'salesChannelDomainId' => $this->getSalesChannelDomain()->getId(),
                    'generateByCronjob' => false,
                    'currencyId' => Defaults::CURRENCY,
                ],
            ], $this->context);
        }
    }

    public function generate():bool
    {
        $csvExporter = $this->exporter->create($this->exporter::TYPE_CSV);

        $criteria = new Criteria();
        $criteria->addAssociation('salesChannelDomain.language');
        $criteria->addAssociation('salesChannel');

        $productExports = $this->productExportRepository->search($criteria, $this->context)->getElements();

        /** @var ProductExportEntity $productExport */
        foreach ($productExports as $productExport){
            $csvExporter->generate($productExport, new ExportBehavior());
        }
        return true;
    }

    private function getSalesChannelDomain(): SalesChannelDomainEntity
    {
        return $this->salesChannelDomainRepository->search(new Criteria(), $this->context)->first();
    }

}
