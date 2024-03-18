<?php
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Configuration;


/**
 * Class ConfigParserUtil
 * @package Elio\ElioDataDiscovery\Configuration
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ConfigParserUtil
{
    /**
     * Prepares a pipe separated values list
     *
     * @param array $config
     * @param string $value
     * @param string $languagePrefix
     * @return string[]
     */
    public static function prepareValueList(array $config, string $value, string $languagePrefix = ''): array
    {
        $valueList = array_key_exists($languagePrefix . $value, $config) ? explode(
            ElioDataDiscoveryConfigService::CONFIG_VALUE_SEPARATOR,
            $config[$languagePrefix . $value] ?? ''
        ) : explode(ElioDataDiscoveryConfigService::CONFIG_VALUE_SEPARATOR, $config[$value] ?? '');
        return array_filter($valueList);
    }

    /**
     * Converts a key value pair string into an associative array
     * key:value|hello:world
     * ->
     * [
     *      "key" => "value",
     *      "hello" => "world"
     * ]
     *
     * @param array $config
     * @param string $value
     * @param string $languagePrefix
     * @return array
     */
    public static function prepareValueListWithKeyValuePair(array $config, string $value, string $languagePrefix = ''): array
    {
        $valueList = self::prepareValueList($config, $value, $languagePrefix);
        $keyValuePairs = [];

        foreach ($valueList as $keyValuePair) {
            $split = explode(':', $keyValuePair);

            if(count($split) === 2) {
                $keyValuePairs[$split[0]] = $split[1];
            }
        }

        return $keyValuePairs;
    }

    /**
     * Returns plugin config for specified key with languagePrefix or default
     * @param array $config
     * @param string $key
     * @param string $languagePrefix
     * @return mixed
     */
    public static function getConfigWithLanguagePrefix(array $config, string $key, string $languagePrefix): mixed
    {
        if (array_key_exists($languagePrefix . $key, $config)) {
            return $config[$languagePrefix . $key];
        }

        return $config[$key] ?? null;
    }
}