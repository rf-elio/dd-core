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

namespace Elio\FactFinder\Service;

/**
 * Class ConfigurationService
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FactFinderConfigService implements FactFinderConfigServiceInterface
{
    public const PLUGIN_CONFIG_PREFIX = 'FactFinder.config';

    private SystemConfigService $systemConfigService;

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @param string $key
     * @param string|null $salesChannelId
     * @return array|mixed|null
     */
    public function get(string $key, ?string $salesChannelId = null)
    {
        return $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX . '.' . $key, $salesChannelId);
    }

    /**
     * @param string|null $salesChannelId
     * @return array
     */
    public function getAll(?string $salesChannelId = null): array
    {
        return $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX, $salesChannelId);
    }

    /**
     * @param string|null $salesChannelId
     * @return array
     */
    public function getApiCredentials(?string $salesChannelId = null): array
    {
        return [
            'apiUrl' => $this->get('apiUrl', $salesChannelId),
            'apiContext' => $this->get('apiContext', $salesChannelId),
            'apiUsername' => $this->get('apiUsername', $salesChannelId),
            'apiPassword' => $this->get('apiPassword', $salesChannelId),
            'apiChannel' => $this->get('apiChannel', $salesChannelId)
        ];
    }
}