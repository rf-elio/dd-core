<?php

namespace Elio\FactFinder\Core\Logging;


use Psr\Log\LoggerInterface;

/**
 * Wrapper for logger to inject additional log handling
 *
 * Class GuzzleLogWrapper
 * @package Elio\FactFinder\Core\Logging
 * @author Ralf Frommherz
 */
class GuzzleLogWrapper implements LoggerInterface
{
    use FactFinderLogTrait;

    private object $sender;
    private array $context;

    /**
     * @param LoggerInterface $logger
     * @param object $sender
     * @param array $context
     */
    public function __construct(LoggerInterface $logger, object $sender, array $context)
    {
        $this->logger = $logger;
        $this->sender = $sender;
        $this->context = $context;
    }

    public function emergency($message, array $context = array())
    {
        $this->ffEmergency($message, $this->sender, array_merge($context, $this->context));
    }

    public function alert($message, array $context = array())
    {
        $this->ffAlert($message, $this->sender, array_merge($context, $this->context));
    }

    public function critical($message, array $context = array())
    {
        $this->ffCritical($message, $this->sender, array_merge($context, $this->context));
    }

    public function error($message, array $context = array())
    {
        $this->ffError($message, $this->sender, array_merge($context, $this->context));
    }

    public function warning($message, array $context = array())
    {
        $this->ffWarning($message, $this->sender, array_merge($context, $this->context));
    }

    public function notice($message, array $context = array())
    {
        $this->ffNotice($message, $this->sender, array_merge($context, $this->context));
    }

    public function info($message, array $context = array())
    {
        $this->ffInfo($message, $this->sender, array_merge($context, $this->context));
    }

    public function debug($message, array $context = array())
    {
        $this->ffDebug($message, $this->sender, array_merge($context, $this->context));
    }

    public function log($level, $message, array $context = array())
    {
        $this->ffLog($level, $message, $this->sender, array_merge($context, $this->context));
    }
}