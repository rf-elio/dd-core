<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Util;

class StringUtil
{
    /**
     * @param string $name
     * @return string
     */
    public static function encodeStringToUnicodeEscaped(string $name) : string
    {
        $specialCharacters = ['(', ')', '[', ']', '<', '>', '`', '.', ',', ':', '=', '!', '&', '|', '$'];
        $encodedName = '';

        foreach (mb_str_split($name) as $char) {
            if (in_array($char, $specialCharacters, true)) {
                $encodedName .= sprintf("\\u%04x", mb_ord($char));
            } else {
                $encodedName .= $char;
            }
        }

        return $encodedName;
    }

    /**
     * @param string $facetName
     * @return string
     */
    public static function decodeStringFromUnescapedUnicode(string $facetName): string
    {
        return preg_replace_callback(
            '/u([0-9a-fA-F]{4})/',
            static function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            },
            $facetName
        );
    }
}