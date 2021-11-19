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

namespace Elio\FactFinder\Core\Util;


/**
 * Class ArrayUtil
 * @package Elio\FactFinder\Core\Util
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ArrayUtil
{
    /**
     * Adds data to an sub array with the given key
     *
     * @param array  $array
     * @param        $data
     * @param array  $keys
     */
    public static function arrayKeyPush(array &$array, $data, ...$keys): void
    {
        if(empty($keys))
        {
            $array[] = $data;
            return;
        }

        $key = $keys[0];
        if(!array_key_exists($key, $array))
        {
            $array[$key] = [];
        }

        self::arrayKeyPush($array[$key], $data, ...array_slice($keys, 1));
    }

    /**
     * Adds data to an sub array with the given key
     *
     * @param array  $array
     * @param        $data
     * @param array  $keys
     */
    public static function arrayKeyAdd(array &$array, $data, ...$keys): void
    {
        $key = $keys[0];
        if(count($keys) === 1)
        {
            $array[$key] = $data;
            return;
        }

        if(!array_key_exists($key, $array))
        {
            $array[$key] = [];
        }

        self::arrayKeyAdd($array[$key], $data, ...array_slice($keys, 1));
    }

    /**
     * Groups an array
     *
     * @param array  $rows
     * @param string $groupColumn
     * @return array
     */
    public static function arrayGroup(array $rows, string $groupColumn) : array
    {
        $groupedArray = array();

        foreach ($rows as $row)
        {
            $groupValue = $row[$groupColumn];

            if(!isset($groupedArray[$groupValue]))
            {
                $groupedArray[$groupValue] = array();
            }

            $groupedArray[$groupValue][] = $row;
        }

        return $groupedArray;
    }

    /**
     * Converts the array to an string representation
     *
     * @param array $values
     * @return string
     */
    public static function convertToString($values) : string
    {
        $string = array();

        if(is_array($values))
        {
            foreach ($values as $key => $value)
            {
                $string[] = $key . ':' . $value;
            }
        }

        return implode(';', $string);
    }

    /**
     * Converts the string representation of an array to an real array
     *
     * @param string $string
     * @return array
     */
    public static function convertStringToArray(string $string) : array
    {
        $values = array();

        foreach(explode(';', $string) as $value)
        {
            $keyValue = explode(':', $value);

            if(isset($keyValue[1]))
            {
                $values[$keyValue[0]] = $keyValue[1];
            }
            else
            {
                $values[] = $keyValue[0];
            }
        }

        return $values;
    }

    /**
     * Returns the array keys as string
     *
     * @param $array
     * @return array
     */
    public static function getArrayKeysAsString($array): array
    {
        return array_map(function ($key) {
            return (string)$key;
        }, array_keys($array));
    }

    /**
     * Creates a new array with one array value as the key and one as the value.
     * If the value key is empty, the while value array will be added as the value.
     *
     * @param array $values
     * @param string $keyKey
     * @param string|null $valueKey
     * @return array
     */
    public static function swap(array $values, string $keyKey, ?string $valueKey = null): array
    {
        $swapedValues = [];

        foreach ($values as $value)
        {
            if(!$valueKey)
            {
                $swapedValues[$value[$keyKey]] = $value;
            }
            else
            {
                $swapedValues[$value[$keyKey]] = $value[$valueKey];
            }
        }

        return $swapedValues;
    }
}