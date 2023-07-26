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

namespace Elio\FactFinder\Core\Tracking\Subscriber;

use Elio\FactFinder\Api\Tracking\Request\CartTrackingRequest;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Tracking\AllowedChecker\TrackingAllowedCheckerInterface;
use Elio\FactFinder\Core\Tracking\Event\CartTrackingRequestCreatedEvent;
use Elio\FactFinder\Core\Tracking\Message\TrackingMessage;
use Elio\FactFinder\Core\Tracking\Utils\TrackingSessionTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class TrackCartSubscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackCartSubscriber implements EventSubscriberInterface
{
    private FactFinderConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private TrackingAllowedCheckerInterface $trackingAllowedChecker;
    private RequestStack $requestStack;
    private EntityRepositoryInterface $productRepository;
    private array $changedQuantities = [];
    use TrackingSessionTrait;

    /**
     * TrackCartSubscriber constructor.
     * @param FactFinderConfigServiceInterface $configService
     * @param TrackingAllowedCheckerInterface $trackingAllowedChecker
     * @param MessageBusInterface $bus
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     * @param EntityRepositoryInterface $productRepository
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        TrackingAllowedCheckerInterface $trackingAllowedChecker,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        EntityRepositoryInterface $productRepository
    )
    {
        $this->configService = $configService;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->trackingAllowedChecker = $trackingAllowedChecker;
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return [
            AfterLineItemAddedEvent::class => 'trackAddCart',
            BeforeLineItemQuantityChangedEvent::class => 'onBeforeLineItemQuantityChangedEvent',
            AfterLineItemQuantityChangedEvent::class => 'trackUpdateCart',
        ];
    }

    /**
     * @param AfterLineItemAddedEvent $event
     */
    public function trackAddCart(AfterLineItemAddedEvent $event): void
    {
        $this->trackCart($event->getSalesChannelContext(), $event->getLineItems());
    }

    /**
     * Tracks quantity increase actions. Only the increased quantity is submitted
     *
     * @param BeforeLineItemQuantityChangedEvent $event
     * @return void
     */
    public function onBeforeLineItemQuantityChangedEvent(BeforeLineItemQuantityChangedEvent $event): void
    {
        $changedLineItem = $event->getLineItem();
        $priceDefinition = $changedLineItem->getPriceDefinition();
        if (!$priceDefinition instanceof QuantityPriceDefinition) {
            return;
        }

        $newQuantity = $changedLineItem->getQuantity();
        $oldQuantity = $priceDefinition->getQuantity();

        // only quantity increases are submitted
        if ($newQuantity <= $oldQuantity) {
            return;
        }

        $this->changedQuantities[$changedLineItem->getReferencedId()] = $newQuantity - $oldQuantity;
    }

    /**
     * @param AfterLineItemQuantityChangedEvent $event
     */
    public function trackUpdateCart(AfterLineItemQuantityChangedEvent $event): void
    {
        // we only want items where the quantity was increased. This is tracked by onBeforeLineItemQuantityChangedEvent
        $itemsWithQuantityIncrease = [];
        foreach ($event->getItems() as $item) {
            $id = $item['id'] ?? '';
            if (isset($this->changedQuantities[$id])) {
                $itemsWithQuantityIncrease[] = [
                    'id' => $id,
                    'quantity' => $this->changedQuantities[$id]
                ];
            }
        }

        $this->trackCart($event->getSalesChannelContext(), $itemsWithQuantityIncrease, $event->getCart());
    }

    /**
     * Tracks the shopware cart
     *
     * @param SalesChannelContext $salesChannelContext
     * @param array $items
     * @param Cart|null $cart
     */
    protected function trackCart(SalesChannelContext $salesChannelContext, array $items, ?Cart $cart = null): void
    {
        $config = $this->configService->getByContext($salesChannelContext);
        $context = $salesChannelContext->getContext();

        if(
            empty($items) ||
            !$config->isActive() ||
            !$config->isTrackCart() ||
            !$this->trackingAllowedChecker->isTrackingAllowed($salesChannelContext)
        ) {
            return;
        }

        $customerId = $salesChannelContext->getCustomer()?->getId();
        $request = new CartTrackingRequest($config->getApiChannel());

        foreach ($items as $item) {
            $lineItem = null;
            $quantity = 0;
            if ($item instanceof LineItem){
                $lineItem = $item;
                $quantity = $lineItem->getQuantity();
            } elseif ($cart !== null && $cart->getLineItems()->has($item['id'])){
                $lineItem = $cart->getLineItems()->get($item['id']);
                $quantity = $item['quantity'];
            }

            if ($lineItem === null || $lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE || !$lineItem->getPrice()) {
                continue;
            }

            /** @var ProductEntity|null $product */
            $product = $this->productRepository->search(new Criteria([$lineItem->getReferencedId()]), $context)->first();
            if (!$product) {
                continue;
            }

            $masterProductNumber = $productNumber = $product->getProductNumber();
            if ($product->getParentId()) {
                /** @var ProductEntity|null $parentProduct */
                $parentProduct = $this->productRepository->search(new Criteria([$product->getParentId()]), $context)->first();

                if ($parentProduct) {
                    $masterProductNumber = $parentProduct->getProductNumber();
                }
            }

            $request->addEvent(
                $productNumber,
                $this->getTrackingSessionId($this->requestStack),
                $masterProductNumber,
                $lineItem->getLabel(),
                $quantity,
                $lineItem->getPrice()->getUnitPrice(),
                $customerId
            );
        }

        $requestCreatedEvent = new CartTrackingRequestCreatedEvent($request);
        $this->eventDispatcher->dispatch($requestCreatedEvent);
        $request = $requestCreatedEvent->getRequest();

        if (!$request->hasEvents()) {
            return;
        }

        $this->bus->dispatch(new TrackingMessage($request, $salesChannelContext->getSalesChannelId()));
    }
}
