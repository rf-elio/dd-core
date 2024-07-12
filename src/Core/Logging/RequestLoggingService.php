<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Logging;

use DateTime;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class RequestLoggingService
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {
    }

    /**
     * @param RequestLoggingInterface $requestLoggingInterface
     * @param SalesChannelContext $salesChannelContext
     * @return void
     */
    public function logRequest(RequestLoggingInterface $requestLoggingInterface, SalesChannelContext $salesChannelContext): void
    {
        $context = $this->prepareSearchRequestLog([$requestLoggingInterface, $salesChannelContext]);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($this);
        $this->logger->info('search', $context);
    }

    /**
     * Prepares the context to extract as much information as possible
     *
     * @param array $context
     * @return array
     */
    protected function prepareSearchRequestLog(array $context) : array
    {
        foreach ($context as $key => $item) {
            if ($item instanceof RequestLoggingInterface) {
                $context[$key] = [
                    'remote_address' => $item->getRemoteAddress(),
                    'search_time' => new DateTime('now'),
                    'request_uri' => $item->getRequestUri(),
                    'user_agent' => $item->getHttpUserAgent(),
                    'type' => get_class($item),
                    'values' => $item
                ];
            }
        }

        $context[LoggingServiceInterface::LOG_ENTRY_ID] = Uuid::randomHex();
        return $context;
    }
}
