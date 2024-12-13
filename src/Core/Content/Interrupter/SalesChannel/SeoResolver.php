<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Content\Interrupter\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SeoResolver
{
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
    ) {}

    public function resolveProductNumbersIntoIds(array $interrupters, SalesChannelContext $context): array
    {
        $productNumbers = [];
        /** @var InterrupterItem $interrupter */
        foreach ($interrupters as $interrupter) {
            $productNumbers[] = $interrupter->getItemId();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        $result = $this->productRepository->searchIds($criteria, $context);

        $productNumberIdMap = [];
        foreach ($result->getData() as $product) {
            $productNumberIdMap[$product['productNumber']] = $product['id'];
        }

        foreach ($interrupters as $interrupter) {
            $interrupter->setItemId($productNumberIdMap[$interrupter->getItemId()]);
        }

        return $interrupters;
    }
}
