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

namespace Elio\FactFinder\Core\Framework\DataAbstractionLayer\Search\AggregationResult;


use Shopware\Core\Framework\Struct\Struct;

/**
 * Class DefaultFacetExtension
 * @package Elio\FactFinder\Core\Framework\DataAbstractionLayer\Search\AggregationResult
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class DefaultFacetExtension extends Struct
{
    public const KEY = 'ff_facet_extension';
    protected const COMBINATION_CHAR = '~';
    protected string $name;
    protected string $value;
    protected string $key;
    protected int $totalHits;

    /**
     * FactFinderDefaultFacetExtension constructor.
     * @param string $name
     * @param string $value
     * @param int $totalHits
     */
    public function __construct(
        string $name,
        string $value,
        int $totalHits
    )
    {
        $this->name = $name;
        $this->value = $value;
        $this->key = $this->name . self::COMBINATION_CHAR . $this->value;
        $this->totalHits = $totalHits;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Separates the combined key into name and value.
     * [$name, $value] = DefaultFacetExtension::parseKey(...);
     *
     * @param string $combinedKey
     * @return array<string>
     */
    public static function parseKey(string $combinedKey): array
    {
        return explode(self::COMBINATION_CHAR, $combinedKey);
    }

    /**
     * @return int
     */
    public function getTotalHits(): int
    {
        return $this->totalHits;
    }
}