<?php
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\FactFinder\Configuration;

use Elio\FactFinder\Configuration\Event\ConfigurationLoadedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use function _PHPStan_68495e8a9\RingCentral\Psr7\parse_query;

/**
 * Class ConfigurationService
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FactFinderConfigService implements FactFinderConfigServiceInterface
{
    public const PLUGIN_CONFIG_PREFIX = 'FactFinder.config';
    protected const CONFIG_VALUE_SEPARATOR = '|';
    private SystemConfigService $systemConfigService;
    private EventDispatcherInterface $eventDispatcher;
    private array $loadedConfigurations = [];

    /**
     * @param SystemConfigService $systemConfigService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Fetches the ff plugin configuration for the given sales channel
     *
     * @param string $salesChannelId
     * @return Configuration
     */
    public function get(string $salesChannelId) : Configuration
    {
        if(isset($this->loadedConfigurations[$salesChannelId])) {
            return $this->loadedConfigurations[$salesChannelId];
        }

        $config = $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX, $salesChannelId);
        parse_str($config['additionalRequestParameters'] ?? '', $additionalRequestParameters);
        $configuration = new Configuration(
            $config['active'],
            $config['apiChannel'],
            $config['apiTimeout'],
            $config['useAso'],
            $config['apiDebugActive'],
            $config['searchUseFactFinder'],
            !empty($config['trackRequireConsent']),
            !empty($config['trackCart']),
            !empty($config['trackCheckout']),
            !empty($config['trackLogin']),
            !empty($config['trackProductView']),
            !empty($config['listingUseFactFinder']),
            $additionalRequestParameters,
            $config['botProtectionActive'],
            $config['botProtectionUseBadBotList'],
            $this->prepareValueList($config, 'botProtectionSearchTermFilter'),
            $this->prepareValueList($config, 'botProtectionUserAgentFilter'),
            $this->prepareValueList($config, 'botProtectionIpFilter'),
            $config['restrictionsParentCategories'] ?? false,
            $config['restrictionsOverridingTopToDown'] ?? false
        );

        $event = new ConfigurationLoadedEvent($configuration, $salesChannelId);
        $this->eventDispatcher->dispatch($event);
        $this->loadedConfigurations[$salesChannelId] = $event->getConfiguration();
        return $event->getConfiguration();
    }

    /***
     * Prepares a pipe separated values list
     *
     * @param array $config
     * @param $value
     * @return false|string[]
     */
    protected function prepareValueList(array $config, $value)
    {
        $valueList = explode(self::CONFIG_VALUE_SEPARATOR, $config[$value] ?? '');
        return array_filter($valueList);
    }

    /**
     * Provides the api credentials.
     *
     * @param string $salesChannelId
     * @return Credentials
     */
    public function getApiCredentials(string $salesChannelId): Credentials
    {
        $config = $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX, $salesChannelId);
        return new Credentials(
            $config['apiUrl'],
            $config['apiUsername'],
            $config['apiPassword'],
        );
    }
}