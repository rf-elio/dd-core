<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Logging;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class RequestLoggingService
{
    use ElioDataDiscoveryLogTrait;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param RequestLoggingInterface $requestLoggingInterface
     * @param SalesChannelContext $context
     * @return void
     */
    public function logRequest(RequestLoggingInterface $requestLoggingInterface, SalesChannelContext $context): void
    {
//        $requestUrl = $requestLoggingInterface->getRequestUri();
//        $remoteAddress = $requestLoggingInterface->getRemoteAddress();
//        $httpUserAgent = $requestLoggingInterface->getHttpUserAgent();

        $this->searchRequestLog('search', $this, [$requestLoggingInterface, $context]);
    }
}
