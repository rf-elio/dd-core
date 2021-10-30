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

namespace Elio\FactFinder\Core\Export;


use Elio\FactFinder\Core\Export\Writer\FileWriterInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class OutputStream
 * @package Elio\FactFinder\Core\Export
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class OutputStream
{
    private FileWriterInterface $writer;
    private ExportEntity $export;
    private SalesChannelContext $context;
    /**
     * @var resource
     */
    private $fileHandle;
    /**
     * @var array<ExportItem>
     */
    private array $buffer = [];

    /**
     * OutputStream constructor.
     * @param FileWriterInterface $writer
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     */
    public function __construct(FileWriterInterface $writer, ExportEntity $export, SalesChannelContext $context)
    {
        $this->writer = $writer;
        $this->export = $export;
        $this->context = $context;
    }

    /**
     * Initializes the output
     */
    public function open(SalesChannelContext $context) : void
    {
        $this->fileHandle = $this->writer->open($context);
    }

    /**
     * Writes new content to the output
     *
     * @param ExportItem $item
     */
    public function write(ExportItem $item) : void
    {
        $this->buffer[] = $item;

        if(count($this->buffer) > 100) {
            $this->writeBuffer();
        }
    }

    /**
     * Submits the buffer
     */
    private function writeBuffer() : void
    {
        if(!empty($this->buffer)) {
            $this->writer->writeList($this->fileHandle, $this->buffer);
            $this->buffer = [];
        }
    }

    /**
     * Aborts the output in case of an error
     */
    public function abort() : void
    {
        $this->writer->abort($this->fileHandle);
    }

    /**
     * Closes the output process
     */
    public function close() : void
    {
        $this->writeBuffer();
        $this->writer->close($this->export, $this->context, $this->fileHandle);
    }
}