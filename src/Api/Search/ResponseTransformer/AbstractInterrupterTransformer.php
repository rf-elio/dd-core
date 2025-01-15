<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search\ResponseTransformer;

use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Response\InterrupterResponse;
use Elio\ElioDataDiscovery\Api\Transform\ResponseTransformerInterface;
use Elio\ElioDataDiscovery\Core\Content\Interrupter\SalesChannel\SeoResolver;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractInterrupterTransformer implements ResponseTransformerInterface
{
    public function __construct(
        private readonly SeoResolver $seoResolver,
    )
    {
    }

    /**
     * @param ResponseCollection $responseCollection
     * @param array $interrupters
     * @param SalesChannelContext $context
     */
    protected function createInterrupterResponse(ResponseCollection $responseCollection, array $interrupters, SalesChannelContext $context): void
    {
        $interrupterResponse = new InterrupterResponse();
        $productInterrupters = [];
        foreach ($interrupters as $interrupter) {
            if ($interrupter->getItemType() === ProductDefinition::ENTITY_NAME) {
                $productInterrupters[] = $interrupter;
                continue;
            }

            $interrupterResponse->addInterrupterItem($interrupter);
        }

        if (!empty($productInterrupters)) {
            $productInterrupters = $this->seoResolver->resolveProductNumbersIntoIds($productInterrupters, $context);
            foreach ($productInterrupters as $interrupter) {
                $interrupterResponse->addInterrupterItem($interrupter);
            }
        }

        $responseCollection->set(InterrupterResponse::class, $interrupterResponse);
    }
}
