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

namespace Elio\FactFinder;

use League\Flysystem\FilesystemInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Elio\FactFinder\Service\Export\ExportManager;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Elio\FactFinder\Service\Export\ExporterInterface;
use Elio\FactFinder\Service\Export\Exporter;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\ProductExport\Service\ProductExportFileHandlerInterface;
use Elio\FactFinder\Service\Export\ExportManagerInterface;

/**
 * Plugin base. It create product export during plugin installation.
 *
 * Class FactFinder
 * @category  Bootstrap
 * @package   Shopware\Plugins\FactFinder
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinder extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);


        /** @var EntityRepositoryInterface $productExportRepository */
        $productExportRepository = $this->container->get("product_export.repository");
        /** @var EntityRepositoryInterface $salesChannelDomainRepository */
        $salesChannelDomainRepository = $this->container->get("sales_channel_domain.repository");
        /** @var EntityRepositoryInterface $productStreamRepository */
        $productStreamRepository = $this->container->get("product_stream.repository");
        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $this->container->get("sales_channel.repository");

        /** @var ProductStreamBuilderInterface $productStreamBuilder */
        $productStreamBuilder = $this->container->get("Shopware\Core\Content\ProductStream\Service\ProductStreamBuilder");
        /** @var SalesChannelRepositoryInterface $productRepository */
        $productRepository = $this->container->get("sales_channel.product.repository");
        /** @var SalesChannelContextServiceInterface $salesChannelContextService */
        //$salesChannelContextService = $this->container->get("Shopware\Core\System\SalesChannel\Context\SalesChannelContextService");
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get("event_dispatcher");
        /** @var int $readBufferSize */
        $readBufferSize = $this->container->getParameter('product_export.read_buffer_size');
        /** @var ProductExportFileHandlerInterface $productExportFileHandler */
        // $productExportFileHandler = $this->container->get("Shopware\Core\Content\ProductExport\Service\ProductExportFileHandler");
        /** @var FilesystemInterface $filesystem */
        //$filesystem = $this->container->get("shopware.filesystem.private");

        /** @var ExporterInterface $exporter */
        /* $exporter = new Exporter(
             $productStreamBuilder,
             $productRepository,
             $salesChannelContextService,
             $eventDispatcher,
             $readBufferSize,
             $productExportFileHandler,
             $filesystem
         );

         $productExportManager = new ExportManager(
             $productExportRepository,
             $salesChannelDomainRepository,
             $productStreamRepository,
             $salesChannelRepository,
             $exporter
         );

         $productExportManager->install();


        /** @var ExportManagerInterface $exportManager */
        #$exportManager = $this->container->get("Elio\FactFinder\Service\Export\ExportManager");
        #$exportManager->install();


    }
}
