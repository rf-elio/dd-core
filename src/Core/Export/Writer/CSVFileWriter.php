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

namespace Elio\FactFinder\Core\Export\Writer;


use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;

/**
 * Class CSVFileWriter
 * @package Elio\FactFinder\Core\Export\Writer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CSVFileWriter extends BaseWriter implements FileWriterInterface
{
    public const TYPE = 'csv';
    private const SEPARATOR = ';';
    private bool $headerWritten = false;

    /**
     * Checks if the writer can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getFormat() === self::TYPE;
    }

    /**
     * @return false|resource
     */
    public function open()
    {
        $this->headerWritten = false;
        return parent::open();
    }

    /**
     * @param resource $handle
     * @param ExportItem $item
     */
    public function write($handle, ExportItem $item): void
    {
        if(!$this->headerWritten) {
            fputcsv($handle, $item->getKeys(), self::SEPARATOR);
            $this->headerWritten = true;
        }

        fputcsv($handle, array_values($item->getParams()), self::SEPARATOR);
    }
}