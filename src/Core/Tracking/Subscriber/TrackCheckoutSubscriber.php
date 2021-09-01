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


use Elio\FactFinder\Api\Tracking\Request\CheckoutTrackingRequest;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Consent\ConsentService;
use Elio\FactFinder\Core\Tracking\Event\CheckoutTrackingRequestCreatedEvent;
use Elio\FactFinder\Core\Tracking\Message\TrackingMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class TrackCheckoutSubscriber
 * @package Elio\FactFinder\Core\Tracking\Subscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackCheckoutSubscriber implements EventSubscriberInterface
{
    private FactFinderConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private ConsentService $consentService;

    /**
     * TrackCheckoutSubscriber constructor.
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
            CheckoutOrderPlacedEvent::class => 'trackCheckout',
        ];
    }

    /**
     * Tracks the checkout event if an order was placed in shopware
     *
     * @param CheckoutOrderPlacedEvent $event
     */
    public function trackCheckout(CheckoutOrderPlacedEvent $event): void
    {
        $order = $event->getOrder();
        $salesChannelId = $event->getSalesChannelId();
        $config = $this->configService->get($salesChannelId);

        if(
            !$config->isActive() ||
            !$config->isTrackCheckout() ||
            !$this->consentService->isTrackingAllowed($salesChannelId) ||
            !$order->getLineItems()
        ) {
            return;
        }

        $customerId = $order->getOrderCustomer() ? $order->getOrderCustomer()->getCustomerId() : null;
        $request = new CheckoutTrackingRequest($config->getApiChannel());

        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $request->addEvent(
                    $lineItem->getReferencedId(),
                    $lineItem->getPayload()['productNumber'] ?? '',
                    $lineItem->getLabel(),
                    $lineItem->getQuantity(),
                    $lineItem->getTotalPrice(),
                    $customerId
                );
            }
        }

        $checkoutTrackingRequestCreatedEvent = new CheckoutTrackingRequestCreatedEvent($event, $request);
        $this->eventDispatcher->dispatch($checkoutTrackingRequestCreatedEvent);
        $this->bus->dispatch(new TrackingMessage(
            $checkoutTrackingRequestCreatedEvent->getRequest(),
            $salesChannelId
        ));
    }
}