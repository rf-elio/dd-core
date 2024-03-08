<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Logging;


use Psr\Log\LoggerInterface;

/**
 * Wrapper for logger to inject additional log handling
 *
 * Class GuzzleLogWrapper
 * @package Elio\ElioSearch\Core\Logging
 * @author Ralf Frommherz
 */
class GuzzleLogWrapper implements LoggerInterface
{
    use ElioSearchLogTrait;

    /**
     * @param LoggerInterface $logger
     * @param object $sender
     * @param array $context
     */
    public function __construct(
        LoggerInterface $logger,
        private object $sender,
        private array $context
    )
    {
        $this->logger = $logger;
    }

    public function emergency($message, array $context = []) : void
    {
        $this->searchEmergency($message, $this->sender, array_merge($context, $this->context));
    }

    public function alert($message, array $context = []) : void
    {
        $this->searchAlert($message, $this->sender, array_merge($context, $this->context));
    }

    public function critical($message, array $context = []) : void
    {
        $this->searchCritical($message, $this->sender, array_merge($context, $this->context));
    }

    public function error($message, array $context = []) : void
    {
        $this->searchError($message, $this->sender, array_merge($context, $this->context));
    }

    public function warning($message, array $context = []) : void
    {
        $this->searchWarning($message, $this->sender, array_merge($context, $this->context));
    }

    public function notice($message, array $context = []) : void
    {
        $this->searchNotice($message, $this->sender, array_merge($context, $this->context));
    }

    public function info($message, array $context = []) : void
    {
        $this->searchInfo($message, $this->sender, array_merge($context, $this->context));
    }

    public function debug($message, array $context = []) : void
    {
        $this->searchDebug($message, $this->sender, array_merge($context, $this->context));
    }

    public function log($level, $message, array $context = []) : void
    {
        $this->searchLog($level, $message, $this->sender, array_merge($context, $this->context));
    }
}