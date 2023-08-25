<?php

namespace Elio\ElioSearch\Core\Logging;


/**
 * Class LoggingService
 *
 * @package Elio\ElioSearch\Core\Logging
 */
interface LoggingServiceInterface
{
    public const LOG_ENTRY_ID = 'log_id';
    public const LOG_ENTRY_SENDER = 'sender';
    public const LOG_ENTRY_SALES_CHANNEL_ID = 'sales_channel_id';
    public const LOG_ENTRY_SALES_CHANNEL_CUSTOMER_GROUP = 'sales_channel_customer_group';
    public const LOG_ENTRY_SALES_CHANNEL_DOMAIN_ID = 'sales_channel_domain_id';
    public const LOG_ENTRY_SCOPE = 'scope';

    /**
     * @return array
     */
    public function getLogs(): array;

    /**
     * @param int $index
     *
     * @return array
     */
    public function getLogContents(int $index): array;

    /**
     * @param int $index
     *
     * @return string
     */
    public function getLogContent(int $index): string;

    /**
     * @param int $index
     */
    public function delete(int $index): void;
}