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

namespace Elio\ElioSearch\Core\Tracking\Subscriber;


use Elio\ElioSearch\Api\Tracking\Request\CheckoutTrackingRequest;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\Tracking\AllowedChecker\TrackingAllowedCheckerInterface;
use Elio\ElioSearch\Core\Tracking\Event\CheckoutTrackingRequestCreatedEvent;
use Elio\ElioSearch\Core\Tracking\Message\TrackingMessage;
use Elio\ElioSearch\Core\Tracking\Utils\TrackingSessionTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class TrackCheckoutSubscriber
 * @package Elio\ElioSearch\Core\Tracking\Subscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackCheckoutSubscriber implements EventSubscriberInterface
{
    private ElioSearchConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private TrackingAllowedCheckerInterface $trackingAllowedChecker;
    private AbstractSalesChannelContextFactory $salesChannelContextFactory;
    private RequestStack $requestStack;
    private EntityRepository $productRepository;
    use TrackingSessionTrait;

    /**
     * TrackCheckoutSubscriber constructor.
     * @param ElioSearchConfigServiceInterface $configService
     * @param TrackingAllowedCheckerInterface $trackingAllowedChecker
     * @param MessageBusInterface $bus
     * @param EventDispatcherInterface $eventDispatcher
     * @param AbstractSalesChannelContextFactory $salesChannelContextFactory
     * @param RequestStack $requestStack
     * @param EntityRepository $productRepository
     */
    public function __construct(
        ElioSearchConfigServiceInterface $configService,
        TrackingAllowedCheckerInterface $trackingAllowedChecker,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher,
        AbstractSalesChannelContextFactory $salesChannelContextFactory,
        RequestStack $requestStack,
        EntityRepository $productRepository
    )
    {
        $this->configService = $configService;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->trackingAllowedChecker = $trackingAllowedChecker;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
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
        $salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(), $salesChannelId, [SalesChannelContextService::LANGUAGE_ID => $order->getLanguageId()]
        );
        $context = $event->getContext();
        $config = $this->configService->getByContext($salesChannelContext);

        if(
            !$config->isActive() ||
            !$config->isTrackCheckout() ||
            !$this->trackingAllowedChecker->isTrackingAllowed($salesChannelContext) ||
            !$order->getLineItems()
        ) {
            return;
        }

        $customerId = $order->getOrderCustomer() ? $order->getOrderCustomer()->getCustomerId() : null;
        $request = new CheckoutTrackingRequest($config->getApiChannel());

        foreach ($order->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
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
                $lineItem->getQuantity(),
                $lineItem->getUnitPrice(),
                $customerId
            );
        }

        $checkoutTrackingRequestCreatedEvent = new CheckoutTrackingRequestCreatedEvent($event, $request);
        $this->eventDispatcher->dispatch($checkoutTrackingRequestCreatedEvent);
        $request = $checkoutTrackingRequestCreatedEvent->getRequest();

        if (!$request->hasEvents()) {
            return;
        }

        $this->bus->dispatch(new TrackingMessage($request, $salesChannelId));
    }
}
