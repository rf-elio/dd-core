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

use Elio\FactFinder\Service\Export\CSV\CSVExporter;
use League\Flysystem\FilesystemInterface;
use Monolog\Logger;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Content\ProductExport\Struct\ExportBehavior;
use Shopware\Core\Content\ProductExport\Struct\ProductExportResult;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\SalesChannelRepositoryIterator;
use Shopware\Core\Content\Product\ProductEntity;
use Elio\FactFinder\Service\FactFinderProductUpdater;
use Shopware\Core\Content\ProductExport\Exception\EmptyExportException;
use Shopware\Core\Content\ProductExport\Event\ProductExportLoggingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\ProductExport\Service\ProductExportFileHandlerInterface;

/**
 * Create an exporter type and generate product export file for that type
 *
 * Class Exporter
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service\Export
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class Exporter implements ExporterInterface
{

    /** @var ProductStreamBuilderInterface */
    protected $productStreamBuilder;

    /** @var SalesChannelRepositoryInterface */
    protected $productRepository;

    /** @var SalesChannelContextServiceInterface */
    protected $salesChannelContextService;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var int */
    protected $readBufferSize;

    /** @var ProductExportFileHandlerInterface */
    protected $productExportFileHandler;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;


    /** @var array */
    protected $headers = [
        'ProductID',
        'MasterProductNumber',
        'Name',
        'Description',
        'ProductURL',
        'Price',
        'Manufacturer',
        'Category',
        'EAN',
        'Keywords'
    ];

    /**
     * @var SeoUrlPlaceholderHandlerInterface
     */
    private $seoUrlReplacer;


    public function __construct(
        ProductStreamBuilderInterface $productStreamBuilder,
        SalesChannelRepositoryInterface $productRepository,
        SalesChannelContextServiceInterface $salesChannelContextService,
        EventDispatcherInterface $eventDispatcher,
        int $readBufferSize,
        ProductExportFileHandlerInterface $productExportFileHandler,
        FilesystemInterface $filesystem,
        SeoUrlPlaceholderHandlerInterface $seoUrlReplacer
    )
    {
        $this->productStreamBuilder = $productStreamBuilder;
        $this->productRepository = $productRepository;
        $this->salesChannelContextService = $salesChannelContextService;
        $this->eventDispatcher = $eventDispatcher;
        $this->readBufferSize = $readBufferSize;
        $this->productExportFileHandler = $productExportFileHandler;
        $this->filesystem = $filesystem;
        $this->seoUrlReplacer = $seoUrlReplacer;
    }

    public function create(int $type): Exporter
    {
        switch ($type) {
            case self::TYPE_CSV:
                $exporter = new CSVExporter(
                    $this->productStreamBuilder,
                    $this->productRepository,
                    $this->salesChannelContextService,
                    $this->eventDispatcher,
                    $this->readBufferSize,
                    $this->productExportFileHandler,
                    $this->filesystem,
                    $this->seoUrlReplacer
                );
                break;
            default:
                throw new \InvalidArgumentException('Unsupported exporter type.');
        }

        return $exporter;
    }


    public function generate(ProductExportEntity $productExport, ExportBehavior $exportBehavior): ?ProductExportResult
    {
        $data = [];
        $contextToken = Uuid::randomHex();
        $context = $this->salesChannelContextService->get(
            $productExport->getStorefrontSalesChannelId(),
            $contextToken,
            $productExport->getSalesChannelDomain()->getLanguageId()
        );

        $filters = $this->productStreamBuilder->buildFilters(
            $productExport->getProductStreamId(),
            $context->getContext()
        );

        $criteria = new Criteria();
        $criteria
            ->addFilter(...$filters)
            ->setOffset($exportBehavior->offset())
            ->setLimit($this->readBufferSize)
            ->addAssociation('categories')
            ->addAssociation('cover')
            ->addAssociation('manufacturer')
            ->addAssociation('media')
            ->addAssociation('prices')
            ->addAssociation('properties.group')
            ->addAssociation('keywords')
            ->addAssociation('seoUrls');

        $iterator = new SalesChannelRepositoryIterator($this->productRepository, $context, $criteria);
        $total = $iterator->getTotal();

        if ($total === 0) {
            $exception = new EmptyExportException($productExport->getId());

            $loggingEvent = new ProductExportLoggingEvent(
                $context->getContext(),
                $exception->getMessage(),
                Logger::WARNING,
                $exception
            );

            $this->eventDispatcher->dispatch($loggingEvent);

            throw $exception;
        }

        $productResult = $iterator->fetch();
        $products = $productResult->getEntities();

        /** @var ProductEntity $product */
        foreach ($products as $product){

            /** @var FactFinderProductUpdater $factFinderProductUpdater */
            $factFinderProductUpdater = new FactFinderProductUpdater($product, $this->seoUrlReplacer);

            $updatedProduct = $factFinderProductUpdater->update();

            if ($productExport->isIncludeVariants() && !$product->getParentId() && $product->getChildCount() > 0) {
                continue; // Skip main product if variants are included
            }
            if (!$productExport->isIncludeVariants() && $product->getParentId()) {
                continue; // Skip variants unless they are included
            }

            /*
            array_push($data, array(
                $updatedProduct->getId(),
                $updatedProduct->getProductNumber(),
                $updatedProduct->getName(),
                $updatedProduct->getDescription(),
                $updatedProduct->getSeoUrls(),
                $updatedProduct->getPrice(),
                $updatedProduct->getManufacturer()->getName(),
                $updatedProduct->getCategoryTree(),
                $updatedProduct->getEan(),
                $updatedProduct->getKeywords(),
            ));
            */

            array_push($data, array(
                $updatedProduct->getId(),
                $updatedProduct->getProductNumber(),
                $updatedProduct->getName(),
                $updatedProduct->getDescription(),
                $factFinderProductUpdater->getSeoUrlDetailPage(),
                null,
                $updatedProduct->getManufacturer()->getName(),
                null,
                $updatedProduct->getEan(),
                $updatedProduct->getKeywords(),
            ));

        }

        //dd($data);

        if (empty($data)) {
            return null;
        }

        $filePath = $this->productExportFileHandler->getFilePath($productExport);

        $this->writeItemsToFile($filePath, $data, $this->headers, ";");

        return new ProductExportResult("", [], $total);
    }

    /**
     * Write data to file format
     *
     * @param string $filename
     * @param array $items
     * @param array $headers
     * @param string $delimiter
     */
    protected function writeItemsToFile(
        string $filePath,
        array $items,
        array $headers = [],
        string $delimiter = ""
    ): void
    {
        // Create a stream opening it with read / write mode
        $stream = fopen('data://text/plain,' . "", 'w+');
        fputcsv($stream, $headers, $delimiter);

        foreach ($items as $item) {
            fputcsv($stream, $item, $delimiter);
        }

        // Rewind the stream
        rewind($stream);

        if(!$this->filesystem->has($filePath)){
            $this->filesystem->write($filePath, stream_get_contents($stream));
        }else{
            $this->filesystem->update($filePath, stream_get_contents($stream));
        }

        fclose($stream);
    }
}
