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

namespace Elio\FactFinder\Core\Tracking\Controller;

use Elio\FactFinder\Api\Tracking\Request\ProductDetailTrackingRequest;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\Consent\ConsentService;
use Elio\FactFinder\Core\Tracking\Event\ProductDetailTrackingRequestCreatedEvent;
use Elio\FactFinder\Core\Tracking\Message\TrackingMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SuccessResponse;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;

/**
 * Class ProductDetailTrackingController
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 * @RouteScope(scopes={"storefront"})
 */
class ProductDetailTrackingController extends StorefrontController
{
    private FactFinderConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private ConsentService $consentService;

    /**
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
     * @Route("/elioFactFinder/productDetailTrack", name="elio-factfinder.product-detail-track", methods={"POST"}, defaults={"XmlHttpRequest"=true,"csrf_protected"=false})
     *
     * @param Request $request
     * @param RequestDataBag $dataBag
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function trackProductDetail(Request $request, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): Response
    {
        $config = $this->configService->get($salesChannelContext->getSalesChannelId());

        if(
            !$config->isActive() ||
            !$config->isTrackProductView() ||
            !$dataBag->has('ffProductTrackingData') ||
            !$this->consentService->isTrackingAllowed($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        ) {
            return new SuccessResponse();
        }

        /** @var RequestDataBag $trackingData */
        $trackingData = $dataBag->get('ffProductTrackingData');
        $customerId = $salesChannelContext->getCustomer() ? $salesChannelContext->getCustomer()->getId() : null;
        $trackingRequest = new ProductDetailTrackingRequest($config->getApiChannel());
        $trackingRequest->addEvent(
            $trackingData->get('id'),
            $salesChannelContext->getToken(),
            $trackingData->get('productNumber'),
            $trackingData->get('label'),
            $trackingData->get('query'),
            $trackingData->get('pos'),
            $trackingData->get('page'),
            $trackingData->get('pageSize'),
            $trackingData->get('campaign'),
            $customerId
        );

        $requestCreatedEvent = new ProductDetailTrackingRequestCreatedEvent($trackingRequest);
        $this->eventDispatcher->dispatch($requestCreatedEvent);
        $this->bus->dispatch(new TrackingMessage(
            $requestCreatedEvent->getRequest(),
            $salesChannelContext->getSalesChannelId()
        ));
        return new SuccessResponse();
    }
}