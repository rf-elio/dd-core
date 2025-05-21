<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Api\Search\ResponseTransformer;

use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Api\Search\ResponseTransformer\Event\ProductListingCriteriaEvent;
use Elio\ElioDataDiscovery\Api\Transform\ResponseTransformerInterface;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\Event\ProductExtensionsLoadedSearchEvent;
use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\DisableVariantGroupingInListingLoaderStruct;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class AbstractProductTransformer
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
abstract class AbstractProductTransformer implements ResponseTransformerInterface
{
    /**
     * AbstractProductTransformer constructor.
     *
     * @param ProductListingLoader $listingLoader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private readonly ProductListingLoader $listingLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    /**
     * @param array $mainNumbers
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @return ProductListingResponse
     */
    public function loadProductsForListing(array $mainNumbers, ResponseCollection $responseCollection, SalesChannelContext $context): ProductListingResponse
    {
        $productNumberSort = array_flip($mainNumbers);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $mainNumbers));
        $criteria->addAssociation('manufacturer');
        $criteria->addExtension(DisableVariantGroupingInListingLoaderStruct::class, new DisableVariantGroupingInListingLoaderStruct());

        $event = new ProductListingCriteriaEvent($mainNumbers, $criteria, $context);
        $this->eventDispatcher->dispatch($event);

        /** @var ProductCollection $products */
        $products = $this->listingLoader->load($event->getCriteria(), $context)->getEntities();

        // sorts the product collection based on the original ff result order
        $products->sort(static function (ProductEntity $a, ProductEntity $b) use ($productNumberSort) {
            $aPosition = $productNumberSort[$a->getProductNumber()] ?? 0;
            $bPosition = $productNumberSort[$b->getProductNumber()] ?? 0;
            return $aPosition <=> $bPosition;
        });

        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
        $responseCollection->set(ProductListingResponse::class, $listing);
        $listing->setProducts($products);

        // total count must be corrected by the difference we have for the found products
        $shouldCount = count($mainNumbers);
        $isCount = $products->count();

        $difference = $shouldCount - $isCount;
        $listing->setTotalHits($listing->getTotalHits() - $difference);
        return $listing;
    }

    /**
     * @param ProductCollection $products
     * @param string $customerId
     * @param SalesChannelContext $context
     * @return ProductCollection
     */
    public function calculateCustomPrices(ProductCollection $products, string $customerId, SalesChannelContext $context): ProductCollection
    {
        $event = new ProductExtensionsLoadedSearchEvent($products, $context);
        $this->eventDispatcher->dispatch($event);

        return $event->getProducts();
    }
}
