<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration;

use Elio\ElioDataDiscovery\Api\Configuration\Request\ConfigurationRequest;
use Elio\ElioDataDiscovery\Api\Configuration\Response\ConfigurationResponseCollection;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ConfigurationAdapter
{
    use ElioDataDiscoveryLogTrait;

    public function __construct(
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function getConfig(ConfigurationRequest $request, SalesChannelContext $context): ConfigurationResponseCollection
    {
        $this->searchDebug('ConfigurationAdapter::getConfig', $this, [$request, $context]);
        return new ConfigurationResponseCollection();
    }
}
