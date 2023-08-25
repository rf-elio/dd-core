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

namespace Elio\ElioSearch\Core\Logging\Subscriber;

use Elio\ElioSearch\Configuration\FactFinderConfigServiceInterface;
use Elio\ElioSearch\Core\Defaults;
use Elio\ElioSearch\Core\Logging\LogFilterContext;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applies the ip filter restriction
 *
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class LogIpFilterSubscriber implements EventSubscriberInterface
{
    private FactFinderConfigServiceInterface $configService;
    private LogFilterContext $logFilterContext;

    /**
     * LogIpFilterSubscriber constructor.
     * @param FactFinderConfigServiceInterface $configService
     * @param LogFilterContext $logFilterContext
     */
    public function __construct(FactFinderConfigServiceInterface $configService, LogFilterContext $logFilterContext)
    {
        $this->configService = $configService;
        $this->logFilterContext = $logFilterContext;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE_POST]
        ];
    }

    /**
     * Activates / deactivates the logging based on the client ip address
     */
    public function onKernelController(ControllerEvent $event) : void
    {
        $request = $event->getRequest();

        /** @var SalesChannelContext|null $context */
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        if(!$context) {
            return;
        }

        $config = $this->configService->getByContext($context);

        if(!$config->isLoggingDebugActive()) {
            $this->logFilterContext->setIsDebugLoggingActive(false);
            return;
        }

        $allowedClientIps = implode(Defaults::VALUE_SEPARATOR, $config->getLoggingDebugIpFilter());
        $clientIp = $request->getClientIp();

        $isApiLoggingActive = empty($allowedClientIps) || strpos($allowedClientIps, $clientIp) !== false;
        $this->logFilterContext->setIsDebugLoggingActive($isApiLoggingActive);
    }
}
