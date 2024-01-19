<?php declare(strict_types=1);
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

namespace Elio\ElioSearch\Configuration;

use Elio\ElioSearch\Configuration\Event\ConfigurationLoadedEvent;
use Elio\ElioSearch\Core\Defaults;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ElioSearchConfigService
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ElioSearchConfigService implements ElioSearchConfigServiceInterface
{
    public const PLUGIN_CONFIG_PREFIX = 'ElioSearch.config';
    public const CONFIG_VALUE_SEPARATOR = Defaults::VALUE_SEPARATOR;
    private SystemConfigService $systemConfigService;
    private EventDispatcherInterface $eventDispatcher;
    private array $loadedConfigurations = [];
    /**
     * @var array<string>
     */
    private array $languagePrefixCache = [];
    private EntityRepository $languageRepository;

    /**
     * @param SystemConfigService $systemConfigService
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityRepository $languageRepository
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        EventDispatcherInterface $eventDispatcher,
        EntityRepository $languageRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Fetches the elio search plugin configuration for the given SalesChannelContext
     *
     * @param SalesChannelContext $salesChannelContext
     * @return Configuration
     */
    public function getByContext(SalesChannelContext $salesChannelContext): Configuration
    {
        return $this->get(
            $salesChannelContext->getSalesChannelId(),
            LanguageHelper::getLanguageIdBySalesChannelContext($salesChannelContext)
        );
    }

    /**
     * Fetches the elio search plugin configuration for the given sales channel
     *
     * @param string $salesChannelId
     * @param string|null $languageId
     * @return Configuration
     */
    public function get(string $salesChannelId, ?string $languageId = null): Configuration
    {
        $languagePrefix = $this->getLanguagePrefix($languageId);

        if (isset($this->loadedConfigurations[$salesChannelId][$languagePrefix])) {
            return $this->loadedConfigurations[$salesChannelId][$languagePrefix];
        }

        $config = $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX, $salesChannelId) ?? [];
        parse_str(
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'additionalRequestParameters', $languagePrefix) ?? '',
            $additionalRequestParameters
        );

        $configuration = new Configuration(
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'active', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'loggingDebugActive', $languagePrefix) ?? false,
            ConfigParserUtil::prepareValueList($config, 'loggingDebugIpFilter', $languagePrefix),
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'searchUseElioSearch', $languagePrefix) ?? false,
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'trackRequireConsent', $languagePrefix)),
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'trackCart', $languagePrefix)),
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'trackCheckout', $languagePrefix)),
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'trackLogin', $languagePrefix)),
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'trackProductView', $languagePrefix)),
            ConfigParserUtil::prepareValueList($config, 'disallowTrackingForUserAgents', $languagePrefix),
            !empty(ConfigParserUtil::getConfigWithLanguagePrefix($config, 'listingUseElioSearch', $languagePrefix)),
            $additionalRequestParameters,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'suggestThumbnailSize', $languagePrefix) ?? 200,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'botProtectionActive', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'botProtectionUseBadBotList', $languagePrefix) ?? false,
            ConfigParserUtil::prepareValueList($config, 'botProtectionSearchTermFilter', $languagePrefix),
            ConfigParserUtil::prepareValueList($config, 'botProtectionUserAgentFilter', $languagePrefix),
            ConfigParserUtil::prepareValueList($config, 'botProtectionIpFilter', $languagePrefix),
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'searchUseContentChannel', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'suggestUseElioSearch', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'restrictionsParentCategories', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'restrictionsOverridingTopToDown', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'restrictionsCacheTime', $languagePrefix) ?? 60,
            ConfigParserUtil::prepareValueListWithKeyValuePair($config, 'suggestTypeLabels', $languagePrefix),
            ConfigParserUtil::prepareValueList($config, 'suggestAcceptedTypes', $languagePrefix),
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'productRankingActive', $languagePrefix) ?? false,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'productRankingMaxOrderAge', $languagePrefix) ?? 14,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'productRankingOrderStates', $languagePrefix) ?? [],
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'productRankingOrderDeliveryStates', $languagePrefix) ?? [],
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'entityStatusMaxCleanupAgeInDays', $languagePrefix) ?? 14,
            ConfigParserUtil::getConfigWithLanguagePrefix($config, 'mergeSortingStrategy', $languagePrefix) ?? false,
        );

        $event = new ConfigurationLoadedEvent($configuration, $salesChannelId);
        $this->eventDispatcher->dispatch($event);
        $this->loadedConfigurations[$salesChannelId][$languagePrefix] = $event->getConfiguration();
        return $event->getConfiguration();
    }

    /**
     * Sets languagePrefix by languageId
     * to fetch plugin configuration based on language
     *
     * @param string|null $languageId
     * @return string
     * @throws InconsistentCriteriaIdsException
     */
    private function getLanguagePrefix(?string $languageId): string
    {
        if(!$languageId) {
            return '';
        }

        if(isset($this->languagePrefixCache[$languageId])) {
            return $this->languagePrefixCache[$languageId];
        }

        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');
        /** @var LanguageEntity|null $language */
        $language = $this->languageRepository->search($criteria, new Context(new SystemSource()))->first();

        if($language && $language->getLocale()) {
            $languagePrefix = $language->getLocale()->getCode() . '_';
            $this->languagePrefixCache[$languageId] = $languagePrefix;
            return $languagePrefix;
        }

        return '';
    }
}
