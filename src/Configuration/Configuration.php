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

namespace Elio\ElioSearch\Configuration;


use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Configuration
 * @package Elio\ElioSearch\Configuration
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Configuration extends Struct
{
    protected bool $loggingDebugActive;
    /**
     * @var array<string>
     */
    private array $loggingDebugIpFilter;
    private bool $searchUseElioSearch;
    private bool $trackCheckout;
    private bool $trackRequireConsent;
    private bool $active;
    private bool $listingUseElioSearch;
    /**
     * @var array<string>
     */
    private array $additionalRequestParameters;
    private int $productThumbnailSize;
    private bool $trackCart;
    private bool $trackLogin;
    private bool $trackProductView;
    private array $disallowTrackingForUserAgents;
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
    private bool $suggestUseElioSearch;
    private bool $restrictionsParentCategories;
    private bool $restrictionsOverridingTopToDown;
    private array $suggestTypeLabels;
    private int $restrictionsCacheTime;
    private array $suggestAcceptedTypes;
    private string $suggestProductNumberAttribute;
    private bool $productRankingActive;
    private int $productRankingMaxOrderAge;
    private array $productRankingOrderStates;
    private array $productRankingOrderDeliveryStates;
    private bool $searchUseContentChannel;
    private int $entityStatusMaxCleanupAgeInDays;
    private bool $mergeSortingStrategy;

    /**
     * Configuration constructor.
     * @param bool $active
     * @param bool $loggingDebugActive
     * @param array<string> $loggingDebugIpFilter
     * @param bool $searchUseElioSearch
     * @param bool $trackRequireConsent
     * @param bool $trackCart
     * @param bool $trackCheckout
     * @param bool $trackLogin
     * @param bool $trackProductView
     * @param array $disallowTrackingForUserAgents
     * @param bool $listingUseElioSearch
     * @param array<string> $additionalRequestParameters
     * @param int $productThumbnailSize
     * @param bool $botProtectionActive
     * @param bool $botProtectionUseBadBotList
     * @param array<string> $botProtectionSearchTermFilter
     * @param array<string> $botProtectionUserAgentFilter
     * @param array<string> $botProtectionIpFilter
     * @param bool $searchUseContentChannel
     * @param bool $suggestUseElioSearch
     * @param bool $restrictionsParentCategories
     * @param bool $restrictionsOverridingTopToDown
     * @param int $restrictionsCacheTime
     * @param array $suggestTypeLabels
     * @param array $suggestAcceptedTypes
     * @param string $suggestProductNumberAttribute
     * @param bool $productRankingActive
     * @param int $productRankingMaxOrderAge
     * @param array $productRankingOrderStates
     * @param array $productRankingOrderDeliveryStates
     * @param int $entityStatusMaxCleanupAgeInDays
     * @param bool $mergeSortingStrategy
     */
    public function __construct(
        bool $active,
        bool $loggingDebugActive,
        array $loggingDebugIpFilter,
        bool $searchUseElioSearch,
        bool $trackRequireConsent,
        bool $trackCart,
        bool $trackCheckout,
        bool $trackLogin,
        bool $trackProductView,
        array $disallowTrackingForUserAgents,
        bool $listingUseElioSearch,
        array $additionalRequestParameters,
        int $productThumbnailSize,
        bool $botProtectionActive,
        bool $botProtectionUseBadBotList,
        array $botProtectionSearchTermFilter,
        array $botProtectionUserAgentFilter,
        array $botProtectionIpFilter,
        bool $searchUseContentChannel,
        bool $suggestUseElioSearch,
        bool $restrictionsParentCategories,
        bool $restrictionsOverridingTopToDown,
        int $restrictionsCacheTime,
        array $suggestTypeLabels,
        array $suggestAcceptedTypes,
        string $suggestProductNumberAttribute,
        bool $productRankingActive,
        int $productRankingMaxOrderAge,
        array $productRankingOrderStates,
        array $productRankingOrderDeliveryStates,
        int $entityStatusMaxCleanupAgeInDays,
        bool $mergeSortingStrategy
    )
    {
        $this->loggingDebugActive = $loggingDebugActive;
        $this->loggingDebugIpFilter = $loggingDebugIpFilter;
        $this->searchUseElioSearch = $searchUseElioSearch;
        $this->trackCheckout = $trackCheckout;
        $this->trackRequireConsent = $trackRequireConsent;
        $this->active = $active;
        $this->listingUseElioSearch = $listingUseElioSearch;
        $this->additionalRequestParameters = $additionalRequestParameters;
        $this->productThumbnailSize = $productThumbnailSize;
        $this->trackCart = $trackCart;
        $this->trackLogin = $trackLogin;
        $this->trackProductView = $trackProductView;
        $this->disallowTrackingForUserAgents = $disallowTrackingForUserAgents;
        $this->botProtectionActive = $botProtectionActive;
        $this->botProtectionUseBadBotList = $botProtectionUseBadBotList;
        $this->botProtectionSearchTermFilter = $botProtectionSearchTermFilter;
        $this->botProtectionUserAgentFilter = $botProtectionUserAgentFilter;
        $this->botProtectionIpFilter = $botProtectionIpFilter;
        $this->suggestUseElioSearch = $suggestUseElioSearch;
        $this->restrictionsParentCategories = $restrictionsParentCategories;
        $this->restrictionsOverridingTopToDown = $restrictionsOverridingTopToDown;
        $this->suggestTypeLabels = $suggestTypeLabels;
        $this->restrictionsCacheTime = $restrictionsCacheTime;
        $this->suggestAcceptedTypes = $suggestAcceptedTypes;
        $this->suggestProductNumberAttribute = $suggestProductNumberAttribute;
        $this->productRankingActive = $productRankingActive;
        $this->productRankingMaxOrderAge = $productRankingMaxOrderAge;
        $this->productRankingOrderStates = $productRankingOrderStates;
        $this->productRankingOrderDeliveryStates = $productRankingOrderDeliveryStates;
        $this->searchUseContentChannel = $searchUseContentChannel;
        $this->entityStatusMaxCleanupAgeInDays = $entityStatusMaxCleanupAgeInDays;
        $this->mergeSortingStrategy = $mergeSortingStrategy;
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
     * @return bool
     */
    public function isSearchUseElioSearch(): bool
    {
        return $this->searchUseElioSearch;
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
    public function isListingUseElioSearch(): bool
    {
        return $this->listingUseElioSearch;
    }

    /**
     * @return array<string>
     */
    public function getAdditionalRequestParameters(): array
    {
        return $this->additionalRequestParameters;
    }

    /**
     * @return int
     */
    public function getProductThumbnailSize(): int
    {
        return $this->productThumbnailSize;
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
     * @return array
     */
    public function getDisallowTrackingForUserAgents(): array
    {
        return $this->disallowTrackingForUserAgents;
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
    public function isSuggestUseElioSearch(): bool
    {
        return $this->suggestUseElioSearch;
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
     * @return bool
     */
    public function isProductRankingActive(): bool
    {
        return $this->productRankingActive;
    }

    /**
     * @return int
     */
    public function getProductRankingMaxOrderAge(): int
    {
        return $this->productRankingMaxOrderAge;
    }

    /**
     * @return array
     */
    public function getProductRankingOrderStates(): array
    {
        return $this->productRankingOrderStates;
    }

    /**
     * @return array
     */
    public function getProductRankingOrderDeliveryStates(): array
    {
        return $this->productRankingOrderDeliveryStates;
    }

    public function isSearchUseContentChannel(): bool
    {
        return $this->searchUseContentChannel;
    }

    public function getEntityStatusMaxCleanupAgeInDays(): int
    {
        return $this->entityStatusMaxCleanupAgeInDays;
    }

    public function isMergeSortingStrategy(): bool
    {
        return $this->mergeSortingStrategy;
    }
}
