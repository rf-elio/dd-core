<?php declare(strict_types=1);

namespace Elio\ElioFactFinder;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Elio\ElioFactFinder\Service\Export\ProductExportManager;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;


class ElioFactFinder extends Plugin
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


        $productExportManager = new ProductExportManager(
            $productExportRepository,
            $salesChannelDomainRepository,
            $productStreamRepository,
            $salesChannelRepository
        );

        $productExportManager->installExports();


    }
}
