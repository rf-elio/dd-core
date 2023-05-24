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

namespace Elio\FactFinder\Core\FilterRestrictions;

use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Search\Request\NavigationRequestProduct;
use Elio\FactFinder\Configuration\FactFinderConfigService;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Adapter\Cache\CacheCompressor;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

/**
 * Class CachedFilterService
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CachedFilterService implements FilterInterface
{

    private FilterInterface $decorated;
    private TagAwareAdapterInterface $cache;
    private FactFinderConfigService $configService;

    /**
     * @param FilterInterface $decorated
     * @param TagAwareAdapterInterface $cache
     * @param FactFinderConfigService $configService
     */
    public function __construct(
        FilterInterface $decorated,
        TagAwareAdapterInterface $cache,
        FactFinderConfigService $configService
    ) {
        $this->decorated = $decorated;
        $this->cache = $cache;
        $this->configService = $configService;
    }

    /**
     * Get all allowed/blocked filters for such salesChannelId and level (if it is category level => categoryId have to be provided)
     *
     * Returns array [
     *              [ array of allowed filters with keys filterId and values filterName],
     *              [ array of blocked filters with keys filterId and values filterName]
     * ]
     *
     * if array of allowed/blocked filters is null - it means allow/block everything
     *
     * @param SalesChannelContext $salesChannelContext
     * @param int $level
     * @param ApiRequest $request
     * @return array
     * @throws InvalidArgumentException|CacheException
     */
    public function getFilters(SalesChannelContext $salesChannelContext, int $level, ApiRequest $request): array
    {
        $config = $this->configService->getByContext($salesChannelContext);
        $cacheTime = $config->getRestrictionsCacheTime();
        $categoryId = $request instanceof NavigationRequestProduct ? $request->getCategoryId() : null;

        $item = $this->cache->getItem(
            $this->generateCacheKey($salesChannelContext, $level, $categoryId)
        );

        try {
            if ($item->isHit() && $item->get()) {
                return CacheCompressor::uncompress($item);
            }
        } catch (Throwable $e) {
        }

        $item->set(
            $this->decorated->getFilters($salesChannelContext, $level, $request)
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
     * @param string|null $categoryId
     * @return string
     */
    public function generateCacheKey(
        SalesChannelContext $salesChannelContext,
        int $level,
        ?string $categoryId = null
    ): string {
        return 'elio_fact_finder.cached_filter_service.' . $salesChannelContext->getSalesChannelId(
            ) . '_' . $level . ($categoryId ? '_' . $categoryId : '');
    }

    /**
     * @return string[]
     */
    private function generateTags(): array
    {
        return ['elio_factfinder_filtersrestrictions'];
    }

    /**
     * Removes cached items with provided keys
     * @param string[] $keys
     * @throws InvalidArgumentException
     */
    public function removeItems(array $keys)
    {
        if ($keys) {
            $this->cache->deleteItems($keys);
        }
    }

    /**
     * Clears the whole cache pool
     * @throws InvalidArgumentException
     */
    public function clearCache()
    {
        $this->cache->invalidateTags($this->generateTags());
    }
}