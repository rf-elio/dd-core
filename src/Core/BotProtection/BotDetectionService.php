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

namespace Elio\FactFinder\Core\BotProtection;


use Elio\FactFinder\Configuration\Configuration;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Elio\FactFinder\Core\BotProtection\Event\BotDetectedEvent;
use Elio\FactFinder\Core\BotProtection\Event\BotDetectionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BotDetectionService
 * @package Elio\FactFinder\Core\BotProtection
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class BotDetectionService implements BotDetectionServiceInterface
{
    private FactFinderConfigServiceInterface $configService;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * BotProtectionService constructor.
     * @param FactFinderConfigServiceInterface $configService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->configService = $configService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Checks the given request for a possible blocked bot
     *
     * @param SalesChannelContext $salesChannelContext
     * @param Request $request
     * @return bool
     */
    public function detect(SalesChannelContext $salesChannelContext, Request $request) : bool
    {
        $config = $this->configService->getByContext($salesChannelContext);

        if (!$config->isBotProtectionActive()) {
            return false;
        }

        if(!$this->performChecks($salesChannelContext->getSalesChannelId(), $config, $request)) {
            return false;
        }

        $detected = $this->performChecks($salesChannelContext->getSalesChannelId(), $config, $request);
        $event = new BotDetectedEvent($salesChannelContext->getSalesChannelId(), $request, $detected);
        $this->eventDispatcher->dispatch($event);
        return $event->isDetected();
    }

    /**
     * Executes all checks to detect an bad bot
     *
     * @param string $salesChannelId
     * @param Configuration $config
     * @param Request $request
     * @return bool
     */
    protected function performChecks(string $salesChannelId, Configuration $config, Request $request): bool
    {
        $event = new BotDetectionEvent($salesChannelId, $request);
        $this->eventDispatcher->dispatch($event);

        if($event->isDetected()) {
            return true;
        }

        // check ip addresses
        $serverVariables = json_encode($request->server->all());
        if($this->checkList($config->getBotProtectionIpFilter(), $serverVariables)) {
            return true;
        }

        // check user agent
        $userAgent = $request->server->get('HTTP_USER_AGENT') ?? '';
        if($this->checkList($config->getBotProtectionUserAgentFilter(), $userAgent)) {
            return true;
        }

        // check invalid search terms
        if($this->checkList($config->getBotProtectionSearchTermFilter(), $request->get('search') ?? '')) {
            return true;
        }

        // checks the predefined bot list
        if($this->checkBotList($config, $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if one of the given entries is part of the block list. All values will be converted to lower case.
     *
     * @param array<string> $blockList
     * @param string $haystack
     * @return bool
     */
    protected function checkList(array $blockList, string $haystack) : bool
    {
        if(empty($haystack)) {
            return false;
        }

        $haystack = strtolower($haystack);
        foreach ($blockList as $value) {
            $value = strtolower($value);
            if(strpos($haystack, $value) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks the predefined bot list
     *
     * @param Configuration $config
     * @param string $userAgent
     * @return bool
     */
    protected function checkBotList(Configuration $config, string $userAgent): bool
    {
        if(!$config->isBotProtectionUseBadBotList()) {
            return false;
        }

        $botListPath = __DIR__.'/../../Resources/files/bot-list.txt';
        $botList = file_get_contents($botListPath);
        $botList = explode(PHP_EOL, $botList);
        return $this->checkList($botList, $userAgent);
    }
}