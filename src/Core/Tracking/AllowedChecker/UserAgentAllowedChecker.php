<?php
/**
 * Copyright (c) 2022, elio GmbH.
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

namespace Elio\ElioSearch\Core\Tracking\AllowedChecker;


use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\Tracking\AllowedChecker\Struct\UserAgent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class UserAgentAllowedChecker
 *
 * @package Elio\ElioSearch\Core\Tracking\AllowedValidator
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Alexander Mikheev <ami@elio-systems.com>
 * @copyright Copyright (c) 2022, elio GmbH (https://www.elio-systems.com)
 */
class UserAgentAllowedChecker implements TrackingAllowedCheckerInterface
{
    public function __construct(
        private readonly ElioSearchConfigServiceInterface $configService
    ) {}

    public function isTrackingAllowed(SalesChannelContext $salesChannelContext): bool
    {
        /** @var UserAgent|null $userAgent */
        $userAgent = $salesChannelContext->getExtension(UserAgent::EXTENSION_KEY);
        if ($userAgent === null) {
            return true;
        }

        $config = $this->configService->getByContext($salesChannelContext);

        foreach ($config->getDisallowTrackingForUserAgents() as $disallowUserAgent) {
            if (mb_stripos($userAgent->getUserAgent(), (string) $disallowUserAgent) !== false) {
                return false;
            }
        }
        return true;
    }

    public function updateContext(?string $userAgent, SalesChannelContext $salesChannelContext): void
    {
        if ($userAgent === null) {
            return;
        }

        $salesChannelContext->addExtension(UserAgent::EXTENSION_KEY, new UserAgent($userAgent));
    }
}
