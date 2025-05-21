<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ProductExtensionsLoadedEvent extends Event
{
    public function __construct(
        private readonly SalesChannelContext $context
    ) {}

    /**
     * @return SalesChannelContext
     */
    public function getContext(): SalesChannelContext
    {
        return $this->context;
    }
}