<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IndexUpdateSubscriber implements EventSubscriberInterface
{
    private bool $isActive = false;

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductLoaded',
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => 'onProductLoaded',
        ];
    }

    public function onProductLoaded(EntityLoadedEvent $event): void
    {
        if($this->isActive) {
            $event->stopPropagation();
        }
    }

    public function setActive(bool $active): void
    {
        $this->isActive = $active;
    }
}
