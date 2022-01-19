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


use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Configuration
 * @package Elio\FactFinder\Configuration
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Configuration extends Struct
{
    protected string $apiChannel;
    protected bool $useAso;
    protected bool $loggingDebugActive;
    /**
     * @var array<string>
     */
    private array $loggingDebugIpFilter;
    private bool $searchUseFactFinder;
    private int $apiTimeout;
    private bool $trackCheckout;
    private bool $trackRequireConsent;
    private bool $active;
    private bool $listingUseFactFinder;
    /**
     * @var array<string>
     */
    private array $additionalRequestParameters;
    private bool $trackCart;
    private bool $trackLogin;
    private bool $trackProductView;
    private bool $botProtectionActive;
    private bool $botProtectionUseBadBotList;
    /**
     * @var array<string>
     */
    private array $botProtectionSearchTermFilter;
    /**
     * @var array<string>
     */
    private array $botProtectionUserAgentFilter;
    /**
     * @var array<string>
     */
    private array $botProtectionIpFilter;
    private bool $suggestUseFactFinder;
    private bool $restrictionsParentCategories;
    private bool $restrictionsOverridingTopToDown;
    private string $apiContentChannel;
    private bool $searchUseContentChannel;
    private array $suggestTypeLabels;
    private int $restrictionsCacheTime;
    private array $suggestAcceptedTypes;
    private string $suggestProductNumberAttribute;
    private int $maxAdvisorProducts;
    private string $searchTermForAdvisorCmsElement;

    /**
     * Configuration constructor.
     * @param bool $active
     * @param string $apiChannel
     * @param int $apiTimeout
     * @param bool $useAso
     * @param bool $loggingDebugActive
     * @param array<string> $loggingDebugIpFilter
     * @param bool $searchUseFactFinder
     * @param bool $trackRequireConsent
     * @param bool $trackCart
     * @param bool $trackCheckout
     * @param bool $trackLogin
     * @param bool $trackProductView
     * @param bool $listingUseFactFinder
     * @param array<string> $additionalRequestParameters
     * @param bool $botProtectionActive
     * @param bool $botProtectionUseBadBotList
     * @param array<string> $botProtectionSearchTermFilter
     * @param array<string> $botProtectionUserAgentFilter
     * @param array<string> $botProtectionIpFilter
     * @param bool $suggestUseFactFinder
     * @param bool $restrictionsParentCategories
     * @param bool $restrictionsOverridingTopToDown
     * @param int $restrictionsCacheTime
     * @param string $apiContentChannel
     * @param bool $searchUseContentChannel
     * @param array $suggestTypeLabels
     * @param array $suggestAcceptedTypes
     * @param string $suggestProductNumberAttribute
     * @param int $maxAdvisorProducts
     * @param string $searchTermForAdvisorCmsElement
     */
    public function __construct(
        bool $active,
        string $apiChannel,
        int $apiTimeout,
        bool $useAso,
        bool $loggingDebugActive,
        array $loggingDebugIpFilter,
        bool $searchUseFactFinder,
        bool $trackRequireConsent,
        bool $trackCart,
        bool $trackCheckout,
        bool $trackLogin,
        bool $trackProductView,
        bool $listingUseFactFinder,
        array $additionalRequestParameters,
        bool $botProtectionActive,
        bool $botProtectionUseBadBotList,
        array $botProtectionSearchTermFilter,
        array $botProtectionUserAgentFilter,
        array $botProtectionIpFilter,
        bool $suggestUseFactFinder,
        bool $restrictionsParentCategories,
        bool $restrictionsOverridingTopToDown,
        int $restrictionsCacheTime,
        string $apiContentChannel,
        bool $searchUseContentChannel,
        array $suggestTypeLabels,
        array $suggestAcceptedTypes,
        string $suggestProductNumberAttribute,
        int $maxAdvisorProducts,
        string $searchTermForAdvisorCmsElement
    )
    {
        $this->useAso = $useAso;
        $this->loggingDebugActive = $loggingDebugActive;
        $this->loggingDebugIpFilter = $loggingDebugIpFilter;
        $this->apiChannel = $apiChannel;
        $this->searchUseFactFinder = $searchUseFactFinder;
        $this->apiTimeout = $apiTimeout;
        $this->trackCheckout = $trackCheckout;
        $this->trackRequireConsent = $trackRequireConsent;
        $this->active = $active;
        $this->listingUseFactFinder = $listingUseFactFinder;
        $this->additionalRequestParameters = $additionalRequestParameters;
        $this->trackCart = $trackCart;
        $this->trackLogin = $trackLogin;
        $this->trackProductView = $trackProductView;
        $this->botProtectionActive = $botProtectionActive;
        $this->botProtectionUseBadBotList = $botProtectionUseBadBotList;
        $this->botProtectionSearchTermFilter = $botProtectionSearchTermFilter;
        $this->botProtectionUserAgentFilter = $botProtectionUserAgentFilter;
        $this->botProtectionIpFilter = $botProtectionIpFilter;
        $this->suggestUseFactFinder = $suggestUseFactFinder;
        $this->restrictionsParentCategories = $restrictionsParentCategories;
        $this->restrictionsOverridingTopToDown = $restrictionsOverridingTopToDown;
        $this->apiContentChannel = $apiContentChannel;
        $this->searchUseContentChannel = $searchUseContentChannel;
        $this->suggestTypeLabels = $suggestTypeLabels;
        $this->restrictionsCacheTime = $restrictionsCacheTime;
        $this->suggestAcceptedTypes = $suggestAcceptedTypes;
        $this->suggestProductNumberAttribute = $suggestProductNumberAttribute;
        $this->maxAdvisorProducts = $maxAdvisorProducts;
        $this->searchTermForAdvisorCmsElement = $searchTermForAdvisorCmsElement;
    }

    /**
     * @return bool
     */
    public function isUseAso(): bool
    {
        return $this->useAso;
    }


    /**
     * @return bool
     */
    public function isLoggingDebugActive(): bool
    {
        return $this->loggingDebugActive;
    }

    /**
     * @return array
     */
    public function getLoggingDebugIpFilter(): array
    {
        return $this->loggingDebugIpFilter;
    }

    /**
     * @return string
     */
    public function getApiChannel(): string
    {
        return $this->apiChannel;
    }

    /**
     * @return bool
     */
    public function isSearchUseFactFinder(): bool
    {
        return $this->searchUseFactFinder;
    }

    /**
     * @return int
     */
    public function getApiTimeout(): int
    {
        return $this->apiTimeout;
    }

    /**
     * @return bool
     */
    public function isTrackCheckout(): bool
    {
        return $this->trackCheckout;
    }

    /**
     * @return bool
     */
    public function isTrackRequireConsent(): bool
    {
        return $this->trackRequireConsent;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isListingUseFactFinder(): bool
    {
        return $this->listingUseFactFinder;
    }

    /**
     * @return array<string>
     */
    public function getAdditionalRequestParameters(): array
    {
        return $this->additionalRequestParameters;
    }

    /**
     * @return bool
     */
    public function isTrackCart(): bool
    {
        return $this->trackCart;
    }

    /**
     * @return bool
     */
    public function isTrackLogin(): bool
    {
        return $this->trackLogin;
    }

    /**
     * @return bool
     */
    public function isTrackProductView(): bool
    {
        return $this->trackProductView;
    }

    /**
     * @return bool
     */
    public function isBotProtectionActive(): bool
    {
        return $this->botProtectionActive;
    }

    /**
     * @return bool
     */
    public function isBotProtectionUseBadBotList(): bool
    {
        return $this->botProtectionUseBadBotList;
    }

    /**
     * @return array<string>
     */
    public function getBotProtectionSearchTermFilter(): array
    {
        return $this->botProtectionSearchTermFilter;
    }

    /**
     * @return array<string>
     */
    public function getBotProtectionUserAgentFilter(): array
    {
        return $this->botProtectionUserAgentFilter;
    }

    /**
     * @return array<string>
     */
    public function getBotProtectionIpFilter(): array
    {
        return $this->botProtectionIpFilter;
    }

    /**
     * @return bool
     */
    public function isSuggestUseFactFinder(): bool
    {
        return $this->suggestUseFactFinder;
    }

    /**
     * @return bool
     */
    public function isRestrictionsParentCategories(): bool
    {
        return $this->restrictionsParentCategories;
    }

    /**
     * @return bool
     */
    public function isRestrictionsOverridingTopToDown(): bool
    {
        return $this->restrictionsOverridingTopToDown;
    }

    /**
     * @return string
     */
    public function getApiContentChannel(): string
    {
        return $this->apiContentChannel;
    }

    /**
     * @return bool
     */
    public function isSearchUseContentChannel(): bool
    {
        return $this->searchUseContentChannel;
    }

    /**
     * @return array
     */
    public function getSuggestTypeLabels(): array
    {
        return $this->suggestTypeLabels;
    }

    /**
     * @return int
     */
    public function getRestrictionsCacheTime(): int
    {
        return $this->restrictionsCacheTime;
    }

    /**
     * @return array
     */
    public function getSuggestAcceptedTypes(): array
    {
        return $this->suggestAcceptedTypes;
    }

    /**
     * @return string
     */
    public function getSuggestProductNumberAttribute(): string
    {
        return $this->suggestProductNumberAttribute;
    }

    /**
     * @return int
     */
    public function getMaxAdvisorProducts(): int
    {
        return $this->maxAdvisorProducts;
    }

    /**
     * @return string
     */
    public function getSearchTermForAdvisorCmsElement(): string
    {
        return $this->searchTermForAdvisorCmsElement;
    }
}
