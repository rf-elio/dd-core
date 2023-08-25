<?php

namespace Elio\ElioSearch\Core\Logging;


use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Trait FactFinderLogTrait
 * @package Elio\ElioSearch\Core\Logging
 * @author Ralf Frommherz
 */
trait FactFinderLogTrait
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffEmergency(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->emergency($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffAlert(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->alert($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffCritical(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->critical($message, $context);
    }


    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffError(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->error($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffWarning(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->warning($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffNotice(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->notice($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffInfo(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->info($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffDebug(string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->debug($message, $context);
    }

    /**
     * Creates enhanced log messages
     *
     * @param mixed $level
     * @param string $message
     * @param object $sender
     * @param array $context
     */
    protected function ffLog(mixed $level, string $message, object $sender, array $context) : void {
        $context = $this->prepareContext($context);
        $context[LoggingServiceInterface::LOG_ENTRY_SENDER] = get_class($sender);
        $this->logger->log($level, $message, $context);
    }

    /**
     * Prepares the context to extract as much information as possible
     *
     * @param array $context
     * @return array
     */
    protected function prepareContext(array $context) : array
    {
        foreach ($context as $key => $item) {
            if ($item instanceof SalesChannelContext) {
                $context[LoggingServiceInterface::LOG_ENTRY_SALES_CHANNEL_ID] = $item->getSalesChannelId();
                $context[LoggingServiceInterface::LOG_ENTRY_SALES_CHANNEL_DOMAIN_ID] = $item->getDomainId();
                $context[LoggingServiceInterface::LOG_ENTRY_SCOPE] = $item->getContext()->getScope();
                $context[LoggingServiceInterface::LOG_ENTRY_SALES_CHANNEL_CUSTOMER_GROUP] = $item->getCurrentCustomerGroup()->getName();
                unset($context[$key]);

            } elseif ($item instanceof Context) {
                $context[LoggingServiceInterface::LOG_ENTRY_SCOPE] = $item->getScope();
                unset($context[$key]);
            } elseif ($item instanceof Throwable) {
                $context[$key] = [
                    'type' => get_class($item),
                    'message' => $item->getMessage(),
                    'file' => $item->getFile(),
                    'line' => $item->getLine(),
                    'trace' => $item->getTraceAsString()
                ];
            }
            elseif (is_object($item)) {
                $context[$key] = [
                    'type' => get_class($item),
                    'values' => $item
                ];
            }
        }

        $context[LoggingServiceInterface::LOG_ENTRY_ID] = Uuid::randomHex();
        return $context;
    }
}