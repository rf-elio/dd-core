<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration;

use Elio\ElioDataDiscovery\Api\Configuration\Request\ConfigurationRequest;
use Elio\ElioDataDiscovery\Api\Configuration\Response\ConfigurationResponse;
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

    public function getPresets(ConfigurationRequest $request, SalesChannelContext $context): ConfigurationResponse
    {
        $this->searchDebug('ConfigurationAdapter::getPresets', $this, [$request, $context]);
        return new ConfigurationResponse();
    }
}
