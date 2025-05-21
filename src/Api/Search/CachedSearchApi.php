<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search;

use Elio\ElioDataDiscovery\Api\Exception\ApiException;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Request\ContentSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\NavigationRequestProduct;
use Elio\ElioDataDiscovery\Api\Search\Request\ProductSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\SearchRequest;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

class CachedSearchApi implements SearchApiInterface
{
    private const CACHE_HEAD_NAME = 'elio-data-discovery-search-api';

    /**
     * @param CacheInterface $cache
     * @param SearchApiInterface $searchApi
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly SearchApiInterface $searchApi,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService
    )
    {
    }

    /**
     * @param ProductSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws Throwable
     */
    public function search(ProductSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $config = $this->configService->getByContext($context);
        $key = $this->generateKey($searchRequest, $context);
        $expiresAfter = $config->getSearchCacheExpiresAfter();

        $compressedResponse = $this->cache->get($key, function (ItemInterface $item) use ($searchRequest, $context, $expiresAfter) {
            $response = $this->searchApi->search($searchRequest, $context);
            $item->expiresAfter($expiresAfter);
            return CacheValueCompressor::compress($response);
        });
        return CacheValueCompressor::uncompress($compressedResponse);
    }

    /**
     * @param ContentSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function searchContent(ContentSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        return $this->searchApi->searchContent($searchRequest, $context);
    }

    /**
     * @param NavigationRequestProduct $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function navigation(NavigationRequestProduct $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        return $this->searchApi->navigation($searchRequest, $context);
    }

    /**
     * @param SearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return string|null
     */
    private function generateKey(
        SearchRequest $searchRequest,
        SalesChannelContext $context
    ): ?string
    {
        $values = $searchRequest->toArray();
        unset($values['requestUri'], $values['remoteAddress'], $values['httpUserAgent']);

        return self::CACHE_HEAD_NAME . '-'
            . $context->getSalesChannelId() . '-'
            . $context->getLanguageId() . '-'
            . md5(json_encode($values));
    }
}
