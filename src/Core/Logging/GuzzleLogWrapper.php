<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Logging;


use Psr\Log\LoggerInterface;

/**
 * Wrapper for logger to inject additional log handling
 *
 * Class GuzzleLogWrapper
 * @package Elio\ElioDataDiscovery\Core\Logging
 * @author Ralf Frommherz
 */
class GuzzleLogWrapper implements LoggerInterface
{
    use ElioDataDiscoveryLogTrait;

    /**
     * @param LoggerInterface $logger
     * @param object $sender
     * @param array $context
     */
    public function __construct(
        LoggerInterface $logger,
        private object  $sender,
        private array   $context
    )
    {
        $this->logger = $logger;
    }

    public function emergency($message, array $context = []): void
    {
        $this->searchEmergency((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function alert($message, array $context = []): void
    {
        $this->searchAlert((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function critical($message, array $context = []): void
    {
        $this->searchCritical((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function error($message, array $context = []): void
    {
        $this->searchError((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function warning($message, array $context = []): void
    {
        $this->searchWarning((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function notice($message, array $context = []): void
    {
        $this->searchNotice((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function info($message, array $context = []): void
    {
        $this->searchInfo((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function debug($message, array $context = []): void
    {
        $this->searchDebug((string)$message, $this->sender, array_merge($context, $this->context));
    }

    public function log($level, $message, array $context = []): void
    {
        $this->searchLog($level, (string)$message, $this->sender, array_merge($context, $this->context));
    }
}