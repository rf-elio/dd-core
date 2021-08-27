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

namespace Elio\FactFinder\Core\Tracking\Message;


use Elio\FactFinder\Api\Tracking\Request\CheckoutTrackingRequest;
use Elio\FactFinder\Api\Tracking\TrackingApi;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler;
use Swagger\Client\ApiException;

/**
 * Class TrackingMessageHandler
 * @package Elio\FactFinder\Core\Tracking\Message
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class TrackingMessageHandler extends AbstractMessageHandler
{
    private TrackingApi $trackingApi;
    private LoggerInterface $logger;

    /**
     * TrackingMessageHandler constructor.
     * @param TrackingApi $trackingApi
     * @param LoggerInterface $logger
     */
    public function __construct(TrackingApi $trackingApi, LoggerInterface $logger)
    {
        $this->trackingApi = $trackingApi;
        $this->logger = $logger;
    }

    /**
     * @param $message
     * @throws ApiException
     */
    public function handle($message): void
    {
        if(!$message instanceof TrackingMessage) {
            throw new \RuntimeException(sprintf(
                'Excepted message of type "%s", got "%s" instead.',
                TrackingMessage::class, get_class($message)
            ));
        }

        $tackingRequest = $message->getRequest();
        if($tackingRequest instanceof CheckoutTrackingRequest) {
            $this->trackingApi->trackCheckout($tackingRequest, $message->getSalesChannelId());
        } else {
            $this->logger->error(sprintf(
                'Not supported tracking request (%s) received by TrackingMessageHandler',
                get_class($tackingRequest)
            ));
            throw new \RuntimeException(sprintf(
                'Not supported tracking request (%s) received',
                get_class($tackingRequest)
            ));
        }
    }

    public static function getHandledMessages(): iterable
    {
        return [TrackingMessage::class];
    }
}