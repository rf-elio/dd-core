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

namespace Elio\ElioSearch\Api\Import\Request;

/**
 * Class SearchImportRequest
 * @package Elio\ElioSearch\Api\Import\Request
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SearchImportRequest extends ImportRequest
{
    public const IMPORT_STAGE_IMPORT_ONLY = 'IMPORT_ONLY';
    public const IMPORT_STAGE_LOAD_ONLY = 'LOAD_ONLY';
    public const IMPORT_STAGE_FULL = 'FULL';

    protected bool $download = true;
    protected bool $cacheFlush = false;
    protected bool $includeCustomerPrices = false;
    protected bool $includeGeo = false;
    protected string $importStage = self::IMPORT_STAGE_FULL;

    /**
     * @return bool
     */
    public function isDownload(): bool
    {
        return $this->download;
    }

    /**
     * @param bool $download
     */
    public function setDownload(bool $download): void
    {
        $this->download = $download;
    }

    /**
     * @return bool
     */
    public function isCacheFlush(): bool
    {
        return $this->cacheFlush;
    }

    /**
     * @param bool $cacheFlush
     */
    public function setCacheFlush(bool $cacheFlush): void
    {
        $this->cacheFlush = $cacheFlush;
    }

    /**
     * @return bool
     */
    public function isIncludeCustomerPrices(): bool
    {
        return $this->includeCustomerPrices;
    }

    /**
     * @param bool $includeCustomerPrices
     */
    public function setIncludeCustomerPrices(bool $includeCustomerPrices): void
    {
        $this->includeCustomerPrices = $includeCustomerPrices;
    }

    /**
     * @return bool
     */
    public function isIncludeGeo(): bool
    {
        return $this->includeGeo;
    }

    /**
     * @param bool $includeGeo
     */
    public function setIncludeGeo(bool $includeGeo): void
    {
        $this->includeGeo = $includeGeo;
    }

    /**
     * @return string
     */
    public function getImportStage(): string
    {
        return $this->importStage;
    }

    /**
     * @param string $importStage
     */
    public function setImportStage(string $importStage): void
    {
        $this->importStage = $importStage;
    }

}
