<?php
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
use Elio\FactFinder\Core\Consent\ConsentService;
use Elio\FactFinder\Core\Tracking\Event\CartTrackingRequestCreatedEvent;
use Elio\FactFinder\Core\Tracking\Message\TrackingMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
    private ConsentService $consentService;

    /**
     * TrackCartSubscriber constructor.
     * @param FactFinderConfigServiceInterface $configService
     * @param ConsentService $consentService
     * @param MessageBusInterface $bus
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        ConsentService $consentService,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->configService = $configService;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->consentService = $consentService;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return [
            AfterLineItemAddedEvent::class => 'trackAddCart',
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
     * @param AfterLineItemQuantityChangedEvent $event
     */
    public function trackUpdateCart(AfterLineItemQuantityChangedEvent $event): void
    {
        $this->trackCart($event->getSalesChannelContext(), $event->getItems(), $event->getCart());
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

        if(
            empty($items) ||
            !$config->isActive() ||
            !$config->isTrackCart() ||
            !$this->consentService->isTrackingAllowed($salesChannelContext)
        ) {
            return;
        }
        $customerId = $salesChannelContext->getCustomer() ? $salesChannelContext->getCustomer()->getId() : null;
        $request = new CartTrackingRequest($config->getApiChannel());
        foreach ($items as $item) {
            $lineItem = null;
            if ($item instanceof LineItem){
                $lineItem = $item;
            }elseif ($cart !== null && $cart->getLineItems()->has($item['id'])){
                $lineItem = $cart->getLineItems()->get($item['id']);
            }

            if ($lineItem !== null && $lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE && $lineItem->getPrice()) {
                $request->addEvent(
                    $lineItem->getReferencedId(),
                    $salesChannelContext->getToken(),
                    $lineItem->getPayload()['productNumber'] ?? '',
                    $lineItem->getLabel(),
                    $lineItem->getQuantity(),
                    $lineItem->getPrice()->getUnitPrice(),
                    $customerId
                );
            }
        }

        $requestCreatedEvent = new CartTrackingRequestCreatedEvent($request);
        $this->eventDispatcher->dispatch($requestCreatedEvent);
        $this->bus->dispatch(new TrackingMessage(
            $requestCreatedEvent->getRequest(),
            $salesChannelContext->getSalesChannelId()
        ));
    }
}