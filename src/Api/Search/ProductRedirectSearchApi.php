<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search;

use Elio\ElioDataDiscovery\Api\Exception\ApiException;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Request\ContentSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\NavigationRequestProduct;
use Elio\ElioDataDiscovery\Api\Search\Request\ProductSearchRequest;
use Elio\ElioDataDiscovery\Api\Search\Response\CampaignRedirectionResponse;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigService;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\AvailableStockAware;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\ProductPageSeoUrlRoute;
use Throwable;

class ProductRedirectSearchApi implements SearchApiInterface
{
    use ElioDataDiscoveryLogTrait;
    use AvailableStockAware;

    /**
     * @param ElioDataDiscoveryConfigService $configService
     * @param SearchApiInterface $searchApi
     * @param SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler
     * @param LoggerInterface $logger
     * @param SalesChannelRepository $productRepository
     * @param SystemConfigService $systemConfigService
     * @param AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
     * @param EntityRepository $salesChannelDomainRepository
     */
    public function __construct(
        private readonly ElioDataDiscoveryConfigService $configService,
        private readonly SearchApiInterface $searchApi,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        LoggerInterface $logger,
        private readonly SalesChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory,
        private readonly EntityRepository $salesChannelDomainRepository
    )
    {
        $this->logger = $logger;
    }

    /**
     * @param ProductSearchRequest $searchRequest
     * @param SalesChannelContext $context
     * @return ResponseCollection
     * @throws ApiException
     * @throws Throwable
     */
    public function search(ProductSearchRequest $searchRequest, SalesChannelContext $context): ResponseCollection
    {
        $config = $this->configService->getByContext($context);
        $searchTerm = $searchRequest->getQuery();

        if (
            $config->isActive()
            && $config->isSearchRedirectToProductDetail($searchTerm)
            && null !== $productId = $this->getProductIdByProductNumber(
                $searchTerm, $context, $this->systemConfigService, $this->productCloseoutFilterFactory
            )
        ) {
            $url = $this->getDomainById($context->getDomainId(), $context)->getUrl();
            if (!$url) {
                return $this->searchApi->search($searchRequest, $context);
            }


            $route = $this->seoUrlPlaceholderHandler->generate(ProductPageSeoUrlRoute::ROUTE_NAME, ['productId' => $productId]);
            $route = $this->seoUrlPlaceholderHandler->replace($route, $url, $context);
            $responseCollection = new ResponseCollection();
            $responseCollection->set(ProductListingResponse::class, ProductListingResponse::createEmpty());
            $responseCollection->set(CampaignRedirectionResponse::class, new CampaignRedirectionResponse(
                '',
                $route
            ));

            return $responseCollection;
        }

        return $this->searchApi->search($searchRequest, $context);
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
     * @param string $searchTerm
     * @param SalesChannelContext $context
     * @param SystemConfigService $systemConfigService
     * @param AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
     * @return string|null
     */
    private function getProductIdByProductNumber(
        string $searchTerm,
        SalesChannelContext $context,
        SystemConfigService $systemConfigService,
        AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    ): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $searchTerm));
        $this->handleAvailableStock($criteria, $context, $systemConfigService, $productCloseoutFilterFactory);
        return $this->productRepository->searchIds($criteria, $context)->firstId();
    }

    /**
     * @param string $domainId
     * @param SalesChannelContext $context
     * @return SalesChannelDomainEntity
     */
    private function getDomainById(string $domainId, SalesChannelContext $context): SalesChannelDomainEntity
    {
        if ($context->getSalesChannel()->getDomains()?->has($domainId)) {
            return $context->getSalesChannel()->getDomains()?->get($domainId);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $domainId));
        return $this->salesChannelDomainRepository->search($criteria, $context->getContext())->getEntities()->first();
    }
}
