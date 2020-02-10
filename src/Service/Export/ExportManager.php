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
use Shopware\Core\Content\ProductExport\Service\ProductExportValidatorInterface;
use Shopware\Core\Content\ProductExport\Struct\ExportBehavior;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
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

    /** @var ProductExportValidatorInterface */
    private $productExportValidator;

    /** @var string */
    private $templateHeader;

    /** @var string */
    private $templateBody;

    /**
     * @param EntityRepositoryInterface $productExportRepository
     * @param EntityRepositoryInterface $saleschannelDomainRepository
     * @param EntityRepositoryInterface $productStreamRepository
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param ExporterInterface $exporter
     * @throws InconsistentCriteriaIdsException
     */
    public function __construct
    (
        EntityRepositoryInterface $productExportRepository,
        EntityRepositoryInterface $saleschannelDomainRepository,
        EntityRepositoryInterface $productStreamRepository,
        EntityRepositoryInterface $salesChannelRepository,
        ExporterInterface $exporter,
        ProductExportValidatorInterface $productExportValidator
    )
    {
        $this->productExportRepository = $productExportRepository;
        $this->salesChannelDomainRepository = $saleschannelDomainRepository;
        $this->productStreamRepository = $productStreamRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->exporter = $exporter;
        $this->productExportValidator = $productExportValidator;

        $this->context = Context::createDefaultContext();
        $criteria = new Criteria();
        $criteria->addAssociation('language');
        $this->salesChannels =  $this->salesChannelRepository->search($criteria, $this->context)->getElements();

        $dir = dirname(__FILE__)."/"."ExportTemplate"."/";
        if(!$this->templateHeader = file_get_contents($dir.self::TEMPLATE_HEADER))
            throw new \Exception("Couldn't read header template file: ".self::TEMPLATE_HEADER);

        if(!$this->templateBody = file_get_contents($dir.self::TEMPLATE_BODY))
            throw new \Exception("Couldn't read body template file: ".self::TEMPLATE_BODY);
    }


    public function install():array
    {
        $ids = [];
        foreach ($this->salesChannels as $salesChannel) {
            $upsertResult = $this->upsertEntity($salesChannel);
            $elements = $upsertResult->getEvents()->getElements();
            if(!empty($elements)){
                foreach ($elements as $element){
                    $ids[] = $element->getIds();
                }
            }
        }
        $installResult[] = [
            "ids" => $ids,
            "errors" => $upsertResult->getErrors(),
        ];
        return $installResult;
    }

    /**
     * @param SalesChannelEntity $salesChannel
     * @return array
     * @throws InconsistentCriteriaIdsException
     */
    private function upsertEntity(SalesChannelEntity $salesChannel): EntityWrittenContainerEvent
    {
        $language = $salesChannel->getLanguage()->getName();
        $filename = "factfinder_".strtolower($salesChannel->getName())."_".strtolower($language).".csv";

        $this->createProductStream();

        $data = [
            'accessKey' => $salesChannel->getAccessKey(),
            'encoding' => ProductExportEntity::ENCODING_UTF8,
            'fileFormat' => ProductExportEntity::FILE_FORMAT_CSV,
            'interval' => 0,
            'headerTemplate' => $this->templateHeader,
            'bodyTemplate' => $this->templateBody,
            'productStreamId' => self::PRODUCT_STREAM_ID,
            'storefrontSalesChannelId' => $this->getSalesChannelDomain()->getSalesChannelId(),
            'salesChannelId' => $salesChannel->getId(),
            'salesChannelDomainId' => $this->getSalesChannelDomain()->getId(),
            'generateByCronjob' => false,
            'currencyId' => Defaults::CURRENCY,
        ];

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('fileName', $filename)
        );

        /** @var ProductExportEntity $productExport */
        $productExport = $this->productExportRepository->search($criteria, $this->context)->first();
        empty($productExport) ? $data['fileName'] = $filename : $data['id'] =  $productExport->getId();

        return $this->productExportRepository->upsert([$data], $this->context);
    }

    private function createProductStream():EntityWrittenContainerEvent
    {
        return $this->productStreamRepository->upsert([
            [
                'id' => self::PRODUCT_STREAM_ID,
                'name' => 'Fact-finder product stream',
                'description' =>'Automatic created product stream to get all active products during product export.',
                'filters' => [["type"=> "multi", "queries"=> [["type"=> "multi",
                    "queries" => [["type"=> "equals", "field"=> "product.active",
                        "value" => "1"]], "operator"=> "AND"]], "operator"=> "OR"]]
            ],
        ], $this->context);
    }

    public function generate():array
    {
        $csvExporter = $this->exporter->create($this->exporter::TYPE_CSV);
        $exportResults = [];
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannelDomain.language');
        $criteria->addAssociation('salesChannel');

        $productExports = $this->productExportRepository->search($criteria, $this->context)->getElements();

        /** @var ProductExportEntity $productExport */
        foreach ($productExports as $productExport){
           $exportResult = $csvExporter->generate($productExport, new ExportBehavior());
           $exportResults[] = [
               "filename" => $productExport->getFileName(),
               "content" => $exportResult->getContent(),
               "errors" => $this->productExportValidator->validate($productExport, $exportResult->getContent()),
               "total" =>$exportResult->getTotal()
           ];
        }
        return $exportResults;
    }

    private function getSalesChannelDomain(): SalesChannelDomainEntity
    {
        return $this->salesChannelDomainRepository->search(new Criteria(), $this->context)->first();
    }

}
