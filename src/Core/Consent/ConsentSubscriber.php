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

namespace Elio\ElioSearch\Core\Consent;


use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextRestoredEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ConsentSubscriber
 * @package Elio\ElioSearch\Core\Consent
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ConsentSubscriber implements EventSubscriberInterface
{
    private ConsentService $consentService;

    /**
     * ConsentSubscriber constructor.
     * @param ConsentService $consentService
     */
    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_CONTEXT_RESOLVE_POST],
            SalesChannelContextRestoredEvent::class => 'onContextRestore'
        ];
    }

    /**
     * Tracks the current consent desicion of the user
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $cookiePreferences = $request->cookies->get('cookie-preference');

        if(empty($cookiePreferences)) {
            return;
        }
        $attributes = $request->attributes;
        /** @var SalesChannelContext|null $context */
        $context = $attributes->has(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT) ?
            $attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT) : null;
        if ($context === null){
            return;
        }
        $trackingCookie = $request->cookies->get('elio_ff_tracking');
        $this->consentService->updateContextIfNecessary(!empty($trackingCookie), $context);
    }

    /**
     * @param SalesChannelContextRestoredEvent $restoredEvent
     */
    public function onContextRestore(SalesChannelContextRestoredEvent $restoredEvent): void
    {
        $request = Request::createFromGlobals();
        $cookiePreferences = $request->cookies->get('cookie-preference');

        if(empty($cookiePreferences)) {
            return;
        }
        $newContext = $restoredEvent->getRestoredSalesChannelContext();
        $trackingCookie = $request->cookies->get('elio_ff_tracking');
        $this->consentService->updateContextIfNecessary(!empty($trackingCookie), $newContext);
    }
}