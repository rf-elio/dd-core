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

namespace Elio\FactFinder\Api\Tracking;


use Elio\FactFinder\Api\ApiClientFactoryInterface;
use Elio\FactFinder\Api\Tracking\Exception\TrackingRequestNotSupportedException;
use Elio\FactFinder\Api\Tracking\Request\CheckoutTrackingRequest;
use Elio\FactFinder\Api\Tracking\Request\LoginTrackingRequest;
use Elio\FactFinder\Api\Tracking\Request\TrackingRequest;
use Psr\Log\LoggerInterface;
use Swagger\Client\ApiException;
use Swagger\Client\Model\CartOrCheckoutEvent;
use Swagger\Client\Model\LoginEvent;

/**
 * Class TrackingApi
 * @package Elio\FactFinder\Api\Tracking
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackingApi
{
    private ApiClientFactoryInterface $apiFactory;
    private LoggerInterface $logger;

    /**
     * SearchApi constructor.
     * @param ApiClientFactoryInterface $apiFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClientFactoryInterface $apiFactory,
        LoggerInterface $logger
    )
    {
        $this->apiFactory = $apiFactory;
        $this->logger = $logger;
    }

    /**
     * Handles the tracking requests
     * @throws ApiException
     */
    public function track(TrackingRequest $request, string $salesChannelId) : void
    {
        $this->logger->error('track received', ['request' => get_class($request)]);
        if(!$request->hasEvents()) {
            return;
        }
        if($request instanceof CheckoutTrackingRequest) {
            $this->trackCheckout($request, $salesChannelId);
            return;
        }
        if($request instanceof LoginTrackingRequest) {
            $this->logger->error('LoginTrackingRequest received', ['request' => get_class($request)]);
            $this->trackLogin($request, $salesChannelId);
            return;
        }

        $this->logger->error('Not supported tracking request received', ['request' => get_class($request)]);
        throw new TrackingRequestNotSupportedException(sprintf(
            'Tracking request "%s" is not supported by "%s".',
            get_class($request), self::class
        ));
    }

    /**
     * @param LoginTrackingRequest $loginTrackingRequest
     * @param string $salesChannelId
     * @throws ApiException
     */
    protected function trackLogin(LoginTrackingRequest $loginTrackingRequest, string $salesChannelId): void
    {
        $apiClient = $this->apiFactory->createTrackingApi($salesChannelId);
        $apiClient->trackLoginUsingPOST(
            $loginTrackingRequest->getChannel(),
            $this->convertLoginEvents($loginTrackingRequest)
        );
    }

    /**
     * Tracks the checkout event
     *
     * @param CheckoutTrackingRequest $checkoutTrackingRequest
     * @param string $salesChannelId
     * @throws ApiException
     */
    protected function trackCheckout(CheckoutTrackingRequest $checkoutTrackingRequest, string $salesChannelId) : void
    {
        $apiClient = $this->apiFactory->createTrackingApi($salesChannelId);
        $apiClient->trackCheckoutUsingPOST(
            $checkoutTrackingRequest->getChannel(),
            $this->convertCheckoutEvents($checkoutTrackingRequest)
        );
    }

    /**
     * @param LoginTrackingRequest $loginTrackingRequest
     * @return LoginEvent[]
     */
    protected function convertLoginEvents(LoginTrackingRequest $loginTrackingRequest): array
    {
        return array_map(static function (array $event) : LoginEvent {
            return new LoginEvent([
                'sid' => $event['sid'],
                'user_id' => $event['userId'],
            ]);
        }, $loginTrackingRequest->getEvents());
    }

    /**
     * @param CheckoutTrackingRequest $checkoutTrackingRequest
     * @return CartOrCheckoutEvent[]
     */
    protected function convertCheckoutEvents(CheckoutTrackingRequest $checkoutTrackingRequest): array
    {
        return array_map(static function (array $event) : CartOrCheckoutEvent {
            return new CartOrCheckoutEvent([
                'id' => $event['id'],
                'master_id' => $event['productNumber'],
                'title' => $event['title'],
                'count' => $event['count'],
                'price' => $event['price'],
                'user_id' => $event['customerId'],
            ]);
        }, $checkoutTrackingRequest->getEvents());
    }
}