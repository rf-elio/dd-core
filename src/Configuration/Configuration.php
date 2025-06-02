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

namespace Elio\ElioDataDiscovery\Configuration;


use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Configuration
 * @package Elio\ElioDataDiscovery\Configuration
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Configuration extends Struct
{
    public function __construct(
        private readonly bool $active,
        protected bool $loggingDebugActive,
        protected bool $loggingSearchRequestActive,
        private readonly array $loggingDebugIpFilter,
        private readonly bool $searchUseElioDataDiscovery,
        private readonly bool $trackRequireConsent,
        private readonly bool $trackCart,
        private readonly bool $trackCheckout,
        private readonly bool $trackLogin,
        private readonly bool $trackProductView,
        private readonly array $disallowTrackingForUserAgents,
        private readonly bool $allowRequestLoggingForTracking,
        private readonly bool $listingUseElioDataDiscovery,
        private readonly bool $productDetailPageCampaignsActive,
        private readonly array $additionalRequestParameters,
        private readonly int $changeSetIndexerBatchSize,
        private readonly int $suggestThumbnailSize,
        private readonly bool $botProtectionActive,
        private readonly bool $botProtectionUseBadBotList,
        private readonly array $botProtectionSearchTermFilter,
        private readonly array $botProtectionUserAgentFilter,
        private readonly array $botProtectionIpFilter,
        private readonly bool $searchUseContentChannel,
        private readonly bool $suggestUseElioDataDiscovery,
        private readonly bool $searchRedirectToProductDetail,
        private readonly string $searchRedirectProductRegex,
        private readonly int $searchCacheExpiresAfter,
        private readonly bool $suggestToggleHighlight,
        private readonly bool $restrictionsParentCategories,
        private readonly bool $restrictionsOverridingTopToDown,
        private readonly int $restrictionsCacheTime,
        private readonly int $navigationStartLevelFilter,
        private readonly array $suggestTypeLabels,
        private readonly array $suggestAcceptedTypes,
        private readonly array $suggestAcceptedTypesMobile,
        private readonly bool $productRankingActive,
        private readonly int $productRankingMaxOrderAge,
        private readonly array $productRankingOrderStates,
        private readonly array $productRankingOrderDeliveryStates,
        private readonly int $entityStatusMaxCleanupAgeInDays,
        private readonly bool $allowStreamIdSearch,
        private readonly int $productDetailSliderLimit,
        private readonly array $recommendationExcludedProducts,
        private readonly string $suggestContainerStyle,
        private readonly string $disabledRecommendationTypes,
        private readonly bool $suggestToggleProductType,
        private readonly string $listingExclusionExpression,
        private readonly bool $resolveCategoriesFromProductStream,
    ) {}

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
    public function isSearchUseElioDataDiscovery(): bool
    {
        return $this->searchUseElioDataDiscovery;
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
    public function isListingUseElioDataDiscovery(): bool
    {
        return $this->listingUseElioDataDiscovery;
    }

    /**
     * @return string[]
     */
    public function getAdditionalRequestParameters(): array
    {
        return $this->additionalRequestParameters;
    }

    /**
     * @return int
     */
    public function getChangeSetIndexerBatchSize(): int
    {
        return $this->changeSetIndexerBatchSize;
    }

    /**
     * @return int
     */
    public function getSuggestThumbnailSize(): int
    {
        return $this->suggestThumbnailSize;
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
    public function isAllowRequestLoggingForTracking(): bool
    {
        return $this->allowRequestLoggingForTracking;
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
    public function isSuggestUseElioDataDiscovery(): bool
    {
        return $this->suggestUseElioDataDiscovery;
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
     * @return int
     */
    public function getNavigationStartLevelFilter(): int
    {
        return $this->navigationStartLevelFilter;
    }

    /**
     * @return array
     */
    public function getSuggestAcceptedTypes(): array
    {
        return $this->suggestAcceptedTypes;
    }

    /**
     * @return array
     */
    public function getSuggestAcceptedTypesMobile(): array
    {
        if (empty($this->suggestAcceptedTypesMobile)) {
            return $this->getSuggestAcceptedTypes();
        }
        return $this->suggestAcceptedTypesMobile;
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

    /**
     * @return bool
     */
    public function isAllowStreamIdSearch(): bool
    {
        return $this->allowStreamIdSearch;
    }

    public function isProductDetailPageCampaignsActive(): bool
    {
        return $this->productDetailPageCampaignsActive;
    }

    public function isSuggestToggleHighlight(): bool
    {
        return $this->suggestToggleHighlight;
    }

    public function getRecommendationExcludedProducts(): array
    {
        return $this->recommendationExcludedProducts;
    }

    public function getProductDetailSliderLimit(): int
    {
        return $this->productDetailSliderLimit;
    }

    public function getSuggestContainerStyle(): string
    {
        return $this->suggestContainerStyle;
    }

    public function isLoggingSearchRequestActive(): bool
    {
        return $this->loggingSearchRequestActive;
    }

    public function isSearchRedirectToProductDetail(string $searchTerm): bool
    {
        if (empty($this->getSearchRedirectProductRegex())) {
            return false;
        }
        if (!preg_match($this->getSearchRedirectProductRegex(), $searchTerm)) {
            return false;
        }
        return $this->searchRedirectToProductDetail;
    }

    public function getSearchRedirectProductRegex(): string
    {
        return $this->searchRedirectProductRegex;
    }

    public function getSearchCacheExpiresAfter(): int
    {
        return $this->searchCacheExpiresAfter;
    }

    public function getDisabledRecommendationTypes(): string
    {
        return $this->disabledRecommendationTypes;
    }

    public function isSuggestToggleProductType(): bool
    {
        return $this->suggestToggleProductType;
    }

    public function getListingExclusionExpression(): string
    {
        return $this->listingExclusionExpression;
    }

    public function isResolveCategoriesFromProductStream(): bool
    {
        return $this->resolveCategoriesFromProductStream;
    }
}
