<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Suggest;

use Elio\ElioDataDiscovery\Api\Search\Request\SuggestRequest;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Core\Suggest\Event\SuggestRequestBuildEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SuggestRequestBuilder
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function build(string $searchTerm, SuggestRequest $request, Configuration $config, SalesChannelContext $context): SuggestRequest
    {
        $request->setQuery($searchTerm);
        $event = new SuggestRequestBuildEvent($request, $searchTerm, $config, $context);
        $this->eventDispatcher->dispatch($event);
        return $event->getRequest();
    }
}
