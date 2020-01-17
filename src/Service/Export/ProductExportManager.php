<?php declare(strict_types=1);

namespace Elio\ElioFactFinder\Service\Export;

use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;


/**
 * Manages the installation and unistallation of the product exports for factfinder
 *
 * Class ProductExportManager
 * @category  Manager Component
 * @package   Shopware\Plugins\ElioFactFinder\Service
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class ProductExportManager
{

    const TEMPLATE = "factfinderBasic";
    const TEMPLATE_BODY_FILE_ENDING = "_body.txt";
    const TEMPLATE_HEADER_FILE_ENDING = "_header.txt";

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
     * @param EntityRepositoryInterface $productExportRepository
     * @param EntityRepositoryInterface $salesChannelDomainRepository
     * @param EntityRepositoryInterface $productStreamRepository
     * @param EntityRepositoryInterface $salesChannelRepository
     */
    public function __construct
    (
        EntityRepositoryInterface $productExportRepository,
        EntityRepositoryInterface $saleschannelDomainRepository,
        EntityRepositoryInterface $productStreamRepository,
        EntityRepositoryInterface $salesChannelRepository
    )
    {
        $this->productExportRepository = $productExportRepository;
        $this->salesChannelDomainRepository = $saleschannelDomainRepository;
        $this->productStreamRepository = $productStreamRepository;
        $this->salesChannelRepository = $salesChannelRepository;

        $this->context = Context::createDefaultContext();
        $this->salesChannels =  $this->salesChannelRepository->search(new Criteria(), $this->context)->getElements();
    }

    /**
     * Creates factfinder csv exports for all sales channels
     *
     * @return boolean
     */
    public function installExports()
    {

        foreach ($this->salesChannels as $salesChannel) {
            //$language = $salesChannel->getLanguage();
            //$localeString = $language->getLocale()->getCode();

            $salesChannelId = $salesChannel->getId();
            $salesChannelName = $salesChannel->getName();


            $filename = "factfinder_".$salesChannelName.".csv";
            $accessKey = $salesChannel->getAccessKey();

            $this->createEntity($filename, $accessKey, $salesChannelId);

        }

        return true;
    }

    /**
     * @param string $filename
     * @param string $accessKey
     * @param string $salesChannelId
     */
    private function createEntity(string $filename, string $accessKey, string $salesChannelId): void
    {

        /*
        $dir = dirname(__FILE__)."/"."ExportTemplate"."/";
        if(!$templateHeader = file_get_contents($dir.self::TEMPLATE.self::TEMPLATE_HEADER_FILE_ENDING))
            throw new \Exception("Couldn't read header template file: ".self::TEMPLATE.self::TEMPLATE_HEADER_FILE_ENDING);

        if(!$templateBody = file_get_contents($dir.self::TEMPLATE.self::TEMPLATE_BODY_FILE_ENDING))
            throw new \Exception("Couldn't read body template file: ".self::TEMPLATE.self::TEMPLATE_BODY_FILE_ENDING);
        */

        /*
        $searchCriteria = new Criteria();
        $searchCriteria->addFilter(new EqualsFilter('file_name', $filename));
        $existingProductExport = $this->productExportRepository->search($searchCriteria, $this->context)->first();
        */


        $this->productExportRepository->upsert([
            [
                'fileName' => $filename,
                'accessKey' => $accessKey,
                'encoding' => ProductExportEntity::ENCODING_UTF8,
                'fileFormat' => ProductExportEntity::FILE_FORMAT_CSV,
                'interval' => 0,
                'headerTemplate' => 'name,url',
                'bodyTemplate' => '{{ product.name }},{{ productUrl(product) }}',
                'productStreamId' => $this->getProductStream()->getId(),
                'storefrontSalesChannelId' => $this->getSalesChannelDomain()->getSalesChannelId(),
                'salesChannelId' => $salesChannelId,
                'salesChannelDomainId' => $this->getSalesChannelDomain()->getId(),
                'generateByCronjob' => false,
                'currencyId' => Defaults::CURRENCY,
            ],
        ], $this->context);



    }

    private function getSalesChannelDomain(): SalesChannelDomainEntity
    {
        return $this->salesChannelDomainRepository->search(new Criteria(), $this->context)->first();
    }

    private function getProductStream(): ProductStreamEntity
    {
        return $this->productStreamRepository->search(new Criteria(), $this->context)->first();
    }





}
