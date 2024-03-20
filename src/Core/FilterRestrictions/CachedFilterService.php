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

namespace Elio\ElioDataDiscovery\Core\FilterRestrictions;

use Elio\ElioDataDiscovery\Api\Request\ApiRequest;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigService;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Adapter\Cache\CacheCompressor;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class CachedFilterService
 * @package Elio\ElioDataDiscovery\Core\FilterRestrictions
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CachedFilterService implements FilterInterface
{

    /**
     * @param FilterInterface $decorated
     * @param TagAwareAdapterInterface $cache
     * @param ElioDataDiscoveryConfigService $configService
     */
    public function __construct(
        private readonly FilterInterface $decorated,
        private readonly TagAwareAdapterInterface $cache,
        private readonly ElioDataDiscoveryConfigService $configService
    ) {}

    /**
     * Returns a list with allowed filter names
     *
     * @param array $items
     * @param string $restrictionType
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     * @return array
     */
    public function filter(array $items, string $restrictionType, ApiRequest $request, SalesChannelContext $context): array
    {
        return $this->decorated->filter($items, $restrictionType, $request, $context);
    }

    /**
     * Gets filters by provided type
     *
     * @param string $type
     * @param SalesChannelContext $context
     * @return EntitySearchResult
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function getFilterByType(string $type, SalesChannelContext $context): EntitySearchResult
    {
        $config = $this->configService->getByContext($context);
        $cacheTime = $config->getRestrictionsCacheTime();

        $item = $this->cache->getItem('elio_data_discovery.cached_filter_service.filters.' . $type);
        try {
            if ($item->isHit() && $item->get()) {
                return CacheCompressor::uncompress($item);
            }
        } catch (Throwable) {
        }

        $item->set(
            $this->decorated->getFilterByType($type, $context)
        );
        $item = CacheCompressor::compress($item, $item->get());
        $item->expiresAfter($cacheTime * 60); // in seconds
        $item->tag($this->generateTags());
        $this->cache->save($item);

        return CacheCompressor::uncompress($item);
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param int $level
     * @param string $type
     * @param string|null $categoryId
     * @return string
     */
    public function generateCacheKey(
        SalesChannelContext $salesChannelContext,
        int $level,
        string $type,
        ?string $categoryId = null
    ): string {
        return 'elio_data_discovery.cached_filter_service.' . $salesChannelContext->getSalesChannelId(
            ) . '_' . $type . '_' . $level . ($categoryId ? '_' . $categoryId : '');
    }

    /**
     * @return string[]
     */
    private function generateTags(): array
    {
        return ['elio_data_discovery_filtersrestrictions'];
    }

    /**
     * Removes cached items with provided keys
     * @param string[] $keys
     * @throws InvalidArgumentException
     */
    public function removeItems(array $keys): void
    {
        if ($keys) {
            $this->cache->deleteItems($keys);
        }
    }

    /**
     * Clears the whole cache pool
     * @throws InvalidArgumentException
     */
    public function clearCache(): void
    {
        $this->cache->invalidateTags($this->generateTags());
    }
}