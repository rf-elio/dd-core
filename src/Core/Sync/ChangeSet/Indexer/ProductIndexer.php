<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer;


use Elio\ElioDataDiscovery\Core\Exception\InvalidTypeException;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer\Event\CriteriaPreparedEvent;
use Elio\ElioDataDiscovery\Core\Sync\DataTypes\ProductDataType;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ProductIndexer
 * @package Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Indexer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductIndexer extends BaseIndexer
{
    public const DATA_TYPE = ProductDataType::class;
    public const ENTITY_TYPE = ProductDefinition::ENTITY_NAME;

    public function __construct(
        SalesChannelRepository $productRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SystemConfigService $systemConfigService,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    ){
        parent::__construct(self::DATA_TYPE, self::ENTITY_TYPE, $productRepository);
    }

    /**
     * Gets criteria
     *
     * @param SalesChannelContext $salesChannelContext
     * @return Criteria
     */
    protected function getCriteria(SalesChannelContext $salesChannelContext): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new OrFilter([
            new EqualsFilter('active', true),
            new AndFilter([
                new EqualsFilter('active', null),
                new EqualsFilter('parent.active', true)
            ])
        ]));
        $criteria->addAssociation('manufacturer.media');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('tags');
        $criteria->addAssociation('elioDataDiscoveryProductSortingTree');

        $salesChannelId = $salesChannelContext->getSalesChannelId();
        $criteria->addFilter(new ProductAvailableFilter($salesChannelId, ProductVisibilityDefinition::VISIBILITY_SEARCH));

        $this->handleAvailableStock($criteria, $salesChannelContext);

        $event = new CriteriaPreparedEvent($this, $criteria, $salesChannelContext);
        $this->eventDispatcher->dispatch($event);
        return $event->getCriteria();
    }

    protected function getEntityIdentifier(Struct $entity): string
    {
        if (!$entity instanceof ProductEntity) {
            throw new InvalidTypeException($entity, ProductEntity::class);
        }
        return $entity->getProductNumber();
    }

    private function handleAvailableStock(Criteria $criteria, SalesChannelContext $context): void
    {
        $salesChannelId = $context->getSalesChannel()->getId();

        $hide = $this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId);

        if (!$hide) {
            return;
        }

        $closeoutFilter = $this->productCloseoutFilterFactory->create($context);
        $criteria->addFilter($closeoutFilter);
    }
}
