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

namespace Elio\FactFinder\Configuration;


use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Configuration
 * @package Elio\FactFinder\Configuration
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Configuration extends Struct
{
    protected string $apiChannel;
    protected bool $useAso;
    protected bool $apiDebugActive;
    private bool $searchUseFactFinder;
    private int $apiTimeout;
    private bool $trackCheckout;
    private bool $trackRequireConsent;

    /**
     * Configuration constructor.
     * @param string $apiChannel
     * @param int $apiTimeout
     * @param bool $useAso
     * @param bool $apiDebugActive
     * @param bool $searchUseFactFinder
     * @param bool $trackRequireConsent
     * @param bool $trackCheckout
     */
    public function __construct(
        string $apiChannel,
        int $apiTimeout,
        bool $useAso,
        bool $apiDebugActive,
        bool $searchUseFactFinder,
        bool $trackRequireConsent,
        bool $trackCheckout
    )
    {
        $this->useAso = $useAso;
        $this->apiDebugActive = $apiDebugActive;
        $this->apiChannel = $apiChannel;
        $this->searchUseFactFinder = $searchUseFactFinder;
        $this->apiTimeout = $apiTimeout;
        $this->trackCheckout = $trackCheckout;
        $this->trackRequireConsent = $trackRequireConsent;
    }

    /**
     * @return bool
     */
    public function isUseAso(): bool
    {
        return $this->useAso;
    }


    /**
     * @return bool
     */
    public function isApiDebugActive(): bool
    {
        return $this->apiDebugActive;
    }

    /**
     * @return string
     */
    public function getApiChannel(): string
    {
        return $this->apiChannel;
    }

    /**
     * @return bool
     */
    public function isSearchUseFactFinder(): bool
    {
        return $this->searchUseFactFinder;
    }

    /**
     * @return int
     */
    public function getApiTimeout(): int
    {
        return $this->apiTimeout;
    }

    /**
     * @return bool
     */
    public function isTrackCheckout(): bool
    {
        return $this->trackCheckout;
    }

    /**
     * @return bool
     */
    public function isTrackRequireConsent(): bool
    {
        return $this->trackRequireConsent;
    }
}