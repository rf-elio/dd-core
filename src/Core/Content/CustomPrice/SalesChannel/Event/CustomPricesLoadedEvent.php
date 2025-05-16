<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\CustomPrice\SalesChannel\Event;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

abstract class CustomPricesLoadedEvent extends Event
{
    public function __construct(
        private readonly string $customerId,
        private readonly SalesChannelContext $context
    ) {}

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return SalesChannelContext
     */
    public function getContext(): SalesChannelContext
    {
        return $this->context;
    }
}
