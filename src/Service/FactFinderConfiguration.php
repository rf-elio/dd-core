<?php declare(strict_types=1);

/**
 * Copyright (c) 2020, elio GmbH.
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

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 *
 * Class FactFinderConfiguration
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinderConfiguration implements FactFinderConfigurationInterface
{

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService;
    }

    private function get(string $key, ?string $salesChannelId = null)
    {
        return $this->configService->get(self::PLUGIN_CONFIG_PREFIX.$key, $salesChannelId);
    }

    public function getRequestProtocol()
    {
        return $this->get('protocol');
    }

    public function getServerAddress()
    {
        return $this->get('ffServer');
    }

    public function getServerPort()
    {
        return $this->get('port');
    }

    public function getContext()
    {
        return $this->get('context');
    }

    public function getFactFinderVersion()
    {
        return $this->get('ffVersion');
    }

    public function getAuthenticationType()
    {
        return $this->get('authtype');
    }

    public function getUserName()
    {
        return $this->get('authusername');
    }

    public function getPassword()
    {
        return $this->get('authpassword');
    }

    public function getAuthenticationPrefix()
    {
        return $this->get('authprefix');
    }

    public function getAuthenticationPostfix()
    {
        return $this->get('authpostfix');
    }

    public function getChannel()
    {
        return $this->get('channel');
    }






}
