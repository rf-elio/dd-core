<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Suggest\Event;

use Elio\ElioDataDiscovery\Api\Search\Request\SuggestRequest;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class SuggestRequestBuildEvent extends Event
{
    public function __construct(
        private SuggestRequest $request,
        private string $searchTerm,
        private Configuration $config,
        private SalesChannelContext $context
    )
    {
    }

    public function getRequest(): SuggestRequest
    {
        return $this->request;
    }

    public function setRequest(SuggestRequest $request): void
    {
        $this->request = $request;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function setConfig(Configuration $config): void
    {
        $this->config = $config;
    }

    public function getContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function setContext(SalesChannelContext $context): void
    {
        $this->context = $context;
    }
}
