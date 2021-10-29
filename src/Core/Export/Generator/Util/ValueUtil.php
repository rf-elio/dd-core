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

namespace Elio\FactFinder\Core\Export\Generator\Util;


/**
 * Class ValueUtil
 * @package Elio\FactFinder\Core\Export\Generator\Util
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ValueUtil
{
    /**
     * Cleans the value for the export
     * - null to string
     * - trim
     * - no xml tags
     *
     * @param string|null $value
     * @return string
     */
    public static function cleanValue(?string $value): string
    {
        $value = empty($value) ? "" : $value;
        $value = trim(strip_tags($value));
        $value = self::replaceCharReferences($value);
        return $value;
    }

    /**
     * Removes duplicate words
     *
     * @param string|null $value
     * @return string
     */
    public static function removeDuplicateWords(?string $value) : string
    {
        if(!$value) {
            return '';
        }

        return implode(',', array_unique(explode(',', $value)));
    }

    /**
     * Replaces html references with the actual char
     *
     * @param string $subject
     * @return string
     */
    protected static function replaceCharReferences(string $subject) : string
    {
        $pattern = '/&[a-z]+;|&|_{2, }/';
        $replacement = '';
        return preg_replace($pattern, $replacement, $subject, -1 );
    }
}