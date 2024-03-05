<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Sorting\Subscriber;

use Elio\ElioSearch\Configuration\ElioSearchConfigService;
use Elio\ElioSearch\Core\Sorting\ProductSortingService;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddProductToSortingTableWrittenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly ProductSortingService $productSortingService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_CATEGORY_WRITTEN_EVENT => 'onProductCategoryWritten'
        ];
    }

    public function onProductCategoryWritten(EntityWrittenEvent $event): void
    {
        if ($this->systemConfigService->get(ElioSearchConfigService::PLUGIN_CONFIG_PREFIX.'.sortingLocation') !== 'sortDisabled') {
            $eventIds = $event->getIds();
            $categoryId = $eventIds[0]['categoryId'] ?? null;
            if ($categoryId) {
                $this->productSortingService->removeProducts();
                $this->productSortingService->addProducts($categoryId);
            }
        }
    }
}
