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

namespace Elio\ElioSearch\Core\Tracking\Subscriber;

use Elio\ElioSearch\Api\Tracking\Request\LoginTrackingRequest;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\Tracking\AllowedChecker\TrackingAllowedCheckerInterface;
use Elio\ElioSearch\Core\Tracking\Event\LoginTrackingRequestCreatedEvent;
use Elio\ElioSearch\Core\Tracking\Message\TrackingMessage;
use Elio\ElioSearch\Core\Tracking\Utils\TrackingSessionTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class TrackLoginSubscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackLoginSubscriber implements EventSubscriberInterface
{
    private ElioSearchConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private TrackingAllowedCheckerInterface $trackingAllowedChecker;
    private RequestStack $requestStack;
    use TrackingSessionTrait;

    /**
     * TrackLoginSubscriber constructor.
     * @param ElioSearchConfigServiceInterface $configService
     * @param TrackingAllowedCheckerInterface $trackingAllowedChecker
     * @param MessageBusInterface $bus
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        ElioSearchConfigServiceInterface $configService,
        TrackingAllowedCheckerInterface $trackingAllowedChecker,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    )
    {
        $this->configService = $configService;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->trackingAllowedChecker = $trackingAllowedChecker;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            CustomerLoginEvent::class => 'trackLogin',
        ];
    }

    /**
     * @param CustomerLoginEvent $event
     */
    public function trackLogin(CustomerLoginEvent $event): void
    {
        $customer = $event->getCustomer();
        $salesChannelId = $event->getSalesChannelId();
        $salesChannelContext = $event->getSalesChannelContext();
        $config = $this->configService->getByContext($event->getSalesChannelContext());

        if(
            !$config->isActive() ||
            !$config->isTrackLogin() ||
            !$this->trackingAllowedChecker->isTrackingAllowed($salesChannelContext) ||
            !$customer->getId()
        ) {
            return;
        }

        $request = new LoginTrackingRequest('');
        $request->addEvent($this->getTrackingSessionId($this->requestStack), $customer->getId());
        $requestCreatedEvent = new LoginTrackingRequestCreatedEvent($request);
        $this->eventDispatcher->dispatch($requestCreatedEvent);

        $this->bus->dispatch(new TrackingMessage(
            $requestCreatedEvent->getRequest(),
            $salesChannelId
        ));
    }

}
