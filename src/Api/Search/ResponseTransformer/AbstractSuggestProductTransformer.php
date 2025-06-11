<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search\ResponseTransformer;

use Elio\ElioDataDiscovery\Api\Event\SuggestProductCollectCriteriaEvent;
use Elio\ElioDataDiscovery\Api\Request\ApiRequest;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Components\SuggestTypes;
use Elio\ElioDataDiscovery\Api\Search\Response\SuggestionResponse;
use Elio\ElioDataDiscovery\Api\Transform\ResponseTransformerInterface;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Elio\ElioDataDiscovery\Core\Suggest\SuggestGroup;
use Elio\ElioDataDiscovery\Core\Suggest\SuggestItem;
use Elio\ElioDataDiscovery\Swagger\ModelInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractSuggestProductTransformer implements ResponseTransformerInterface
{
    public function __construct(
        protected readonly SalesChannelRepository $productRepository,
        protected readonly ElioDataDiscoveryConfigServiceInterface $configService,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function transform(ModelInterface $model, ResponseCollection $responseCollection, SalesChannelContext $context, ApiRequest $request): void
    {
        /** @var SuggestionResponse|null $suggestionResponse */
        $suggestionResponse = $responseCollection->get(SuggestionResponse::class) ?? new SuggestionResponse();
        $responseCollection->set(SuggestionResponse::class, $suggestionResponse);
        $config = $this->configService->getByContext($context);
        $groupLabels = $config->getSuggestTypeLabels();

        $productGroupKey = $groupLabels[SuggestTypes::PRODUCT->value] ?? SuggestTypes::PRODUCT->value;

        if (!$suggestionResponse || !$suggestionResponse->hasGroup($productGroupKey)) {
            return;
        }

        $productGroup = $suggestionResponse->getGroup($productGroupKey);
        $products = $this->collect($productGroup, $config, $context);
        $this->enrich($productGroup, $products, $config, $context);
    }

    protected function collect(SuggestGroup $group, Configuration $config, SalesChannelContext $context): array
    {
        $productNumbers = [];
        foreach ($group->getItems() as $item) {
            if($productNumber = $this->getProductNumber($item)) {
                $productNumbers[] = $productNumber;
            }
        }

        if(empty($productNumbers)) {
            return [];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        $event = new SuggestProductCollectCriteriaEvent($productNumbers, $criteria, $context);
        $this->eventDispatcher->dispatch($event);

        $products = [];

        /** @var ProductEntity $product */
        foreach ($this->productRepository->search($event->getCriteria(), $context) as $product) {
            $products[$product->getProductNumber()] = $product;
        }

        return $products;
    }

    protected function enrich(SuggestGroup $group, array $products, Configuration $config, SalesChannelContext $context): void
    {
        foreach ($group->getItems() as $item) {
            $productNumber = $this->getProductNumber($item);
            if($productNumber && isset($products[$productNumber])) {
                $item->setEntity($products[$productNumber]);
            }
        }
    }

    abstract protected function getProductNumber(SuggestItem $item): ?string;
}
