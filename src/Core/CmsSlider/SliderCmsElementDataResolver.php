<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\CmsSlider;

use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Api\Search\SearchApiInterface;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\ProductSearchRequestBuilder;
use Elio\ElioDataDiscovery\Core\Logging\ElioDataDiscoveryLogTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductSliderStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class SliderCmsElementDataResolver extends AbstractCmsElementResolver
{
    use ElioDataDiscoveryLogTrait;

    public function __construct(
        private readonly ProductSearchRequestBuilder $searchRequestBuilder,
        private readonly SearchApiInterface $searchApi,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function getType(): string
    {
        return 'edd-cms-slider';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        try {
            $salesChannelContext = $resolverContext->getSalesChannelContext();
            $config = $this->configService->getByContext($salesChannelContext);
            if (!$config->isActive()) {
                return;
            }

            $request = $resolverContext->getRequest();
            $config = $slot->getFieldConfig();
            $name = $config->get('cmsSliderParameterName');
            $value = $config->get('cmsSliderParameterValue');
            $searchRequest = $this->searchRequestBuilder->build($request, new Criteria(), $salesChannelContext);
            $searchRequest->setAdditionalRequestParameters([
                'name' => $name->getValue(),
                'preset' => $value->getValue()
            ]);
            $responseCollection = $this->searchApi->search($searchRequest, $salesChannelContext);
            $productListing = $responseCollection->get(ProductListingResponse::class);
            $slider = new ProductSliderStruct();
            $slider->setProducts($productListing->getProducts());
            $slot->setData($slider);
        } catch (\Throwable $e) {
            $this->searchError('Could not enrich preset slider slider element', $this, [
                'exception' => $e,
                'slot' => $slot,
                'resolverContext' => $resolverContext
            ]);
        }
    }
}
