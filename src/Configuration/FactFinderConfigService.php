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
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
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
    private string $languagePrefix = '';
    private EntityRepositoryInterface $languageRepository;

    /**
     * @param SystemConfigService $systemConfigService
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityRepositoryInterface $languageRepository
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $languageRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Fetches the ff plugin configuration for the given SalesChannelContext
     *
     * @param SalesChannelContext $salesChannelContext
     * @return Configuration
     */
    public function getByContext(SalesChannelContext $salesChannelContext): Configuration
    {
        if(count($salesChannelContext->getLanguageIdChain()) > 0) {
            $languageId = $salesChannelContext->getLanguageIdChain()[0];
            $this->setLanguagePrefix($languageId);
        }

        return $this->get($salesChannelContext->getSalesChannelId());
    }

    /**
     * Fetches the ff plugin configuration for the given sales channel
     *
     * @param string $salesChannelId
     * @return Configuration
     */
    public function get(string $salesChannelId): Configuration
    {
        if (isset($this->loadedConfigurations[$salesChannelId][$this->languagePrefix])) {
            return $this->loadedConfigurations[$salesChannelId][$this->languagePrefix];
        }

        $config = $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX, $salesChannelId);
        parse_str(
            $this->getConfigWLangPrefix($config, 'additionalRequestParameters') ?? '',
            $additionalRequestParameters
        );
        $configuration = new Configuration(
            $this->getConfigWLangPrefix($config, 'active'),
            $this->getConfigWLangPrefix($config, 'apiChannel'),
            $this->getConfigWLangPrefix($config, 'apiTimeout'),
            $this->getConfigWLangPrefix($config, 'useAso'),
            $this->getConfigWLangPrefix($config, 'apiDebugActive'),
            $this->getConfigWLangPrefix($config, 'searchUseFactFinder'),
            !empty($this->getConfigWLangPrefix($config, 'trackRequireConsent')),
            !empty($this->getConfigWLangPrefix($config, 'trackCart')),
            !empty($this->getConfigWLangPrefix($config, 'trackCheckout')),
            !empty($this->getConfigWLangPrefix($config, 'trackLogin')),
            !empty($this->getConfigWLangPrefix($config, 'trackProductView')),
            !empty($this->getConfigWLangPrefix($config, 'listingUseFactFinder')),
            $additionalRequestParameters,
            $this->getConfigWLangPrefix($config, 'botProtectionActive'),
            $this->getConfigWLangPrefix($config, 'botProtectionUseBadBotList'),
            $this->prepareValueList($config, 'botProtectionSearchTermFilter'),
            $this->prepareValueList($config, 'botProtectionUserAgentFilter'),
            $this->prepareValueList($config, 'botProtectionIpFilter')
        );

        $event = new ConfigurationLoadedEvent($configuration, $salesChannelId);
        $this->eventDispatcher->dispatch($event);
        $this->loadedConfigurations[$salesChannelId][$this->languagePrefix] = $event->getConfiguration();
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
        $valueList = key_exists($this->languagePrefix . $value, $config) ? explode(
            self::CONFIG_VALUE_SEPARATOR,
            $config[$this->languagePrefix . $value] ?? ''
        ) : explode(self::CONFIG_VALUE_SEPARATOR, $config[$value] ?? '');
        return array_filter($valueList);
    }

    /**
     * Returns plugin config for specified key with languagePrefix or default
     * @param array $config
     * @param string $key
     * @return mixed|string
     */
    private function getConfigWLangPrefix(array $config, string $key)
    {
        if (key_exists($this->languagePrefix . $key, $config)) {
            return $config[$this->languagePrefix . $key];
        } else {
            if (key_exists($key, $config)) {
                return $config[$key];
            } else {
                return null;
            }
        }
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

    /**
     * Sets languagePrefix by languageId
     * to fetch plugin configuration based on language
     *
     * @param string $languageId
     */
    public function setLanguagePrefix(string $languageId): void
    {
        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');
        $language = $this->languageRepository->search($criteria, Context::createDefaultContext())->first();

        /** @var LanguageEntity $language */
        if($language && $language->getLocale()) {
            $this->languagePrefix = $language->getLocale()->getCode() . '_';
        } else {
            $this->languagePrefix = '';
        }
    }
}