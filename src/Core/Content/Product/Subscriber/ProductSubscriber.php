<?php
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

namespace Elio\ElioDataDiscovery\Core\Content\Product\Subscriber;


use Elio\ElioDataDiscovery\Core\Content\Product\SalesChannel\MainVariantMappingExtension;
use Elio\ElioDataDiscovery\Core\Sync\RatingCountService;
use Shopware\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductSubscriber
 *
 * @package Elio\ElioDataDiscovery\Core\Content\Product\Subscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Alexander Mikheev <ami@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RatingCountService $ratingCountService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingResolvePreviewEvent::class => 'onProductListingResolvePreview',
            ProductEvents::PRODUCT_REVIEW_WRITTEN_EVENT => 'onProductReviewWritten',
            ProductEvents::PRODUCT_REVIEW_DELETED_EVENT => 'onProductReviewDeleted'
        ];
    }

    public function onProductListingResolvePreview(ProductListingResolvePreviewEvent $event): void
    {
        $mainVariantMapping = $event->getContext()->getExtension(MainVariantMappingExtension::KEY);
        if (!$mainVariantMapping instanceof MainVariantMappingExtension) {
            return;
        }
        $mainVariantMapping = $mainVariantMapping->getMapping();

        foreach ($event->getMapping() as $id => $mappedId) {
            if (array_key_exists($id, $mainVariantMapping)) {
                $event->replace($id, $mainVariantMapping[$id]);
            }
        }
    }

    public function onProductReviewWritten(EntityWrittenEvent $event): void
    {
        $reviewIds = [];
        foreach ($event->getWriteResults() as $result) {
            $reviewIds[] = $result->getPrimaryKey();
        }
        $productIds = $this->ratingCountService->getProductsFromReviews($event->getContext(), $reviewIds);
        $this->ratingCountService->updateProductRatingCounts($event->getContext(), $productIds);
    }

    public function onProductReviewDeleted(EntityDeletedEvent $event): void
    {
        $productIds = [];
        foreach ($event->getWriteResults() as $result) {
            $productIds[] = Uuid::fromBytesToHex($result->getChangeSet()?->getBefore('product_id'));
        }
        $this->ratingCountService->updateProductRatingCounts($event->getContext(), $productIds);
    }
}
