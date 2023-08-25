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

namespace Elio\ElioSearch\Core\Export\Writer;


use Elio\ElioSearch\Core\Export\Exception\ExportValidationException;
use Elio\ElioSearch\Core\Export\ExportEntity;
use Elio\ElioSearch\Core\Export\ExportItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CSVFileWriter
 * @package Elio\ElioSearch\Core\Export\Writer
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
     * @return resource
     */
    public function open(SalesChannelContext $context)
    {
        $this->headerWritten = false;
        return parent::open($context);
    }

    /**
     * @param resource $handle
     * @param ExportItem $item
     */
    protected function write($handle, ExportItem $item): void
    {
        if(!$this->headerWritten) {
            fputcsv($handle, $this->model, self::SEPARATOR);
            $this->headerWritten = true;
        }

        $output = $item->getParams();
        $orderedOutput = [];

        foreach ($this->model as $key) {
            $orderedOutput[] = $output[$key] ?? '';
        }

        $this->validateRow($orderedOutput);
        fputcsv($handle, $orderedOutput, self::SEPARATOR);
    }

    /**
     * Checks if the given row is valid. Performed validations:
     * - Col count check
     * - Line feed check
     *
     * @param array $row
     * @return void
     */
    protected function validateRow(array $row): void
    {
        $shouldColumnCount = count($this->model);
        $isColumnCount = count($row);

        if ($shouldColumnCount !== $isColumnCount) {
            throw new ExportValidationException(sprintf(
                'Export row has %s columns, but should have %d columns',
                $isColumnCount, $shouldColumnCount
            ));
        }

        foreach ($row as $key => $value) {
            if (str_contains($value, PHP_EOL)) {
                throw new ExportValidationException(sprintf(
                    'Export row contains not allowed line feed in column "%s" (%s)',
                    $key, json_encode($row)
                ));
            }
        }
    }
}
