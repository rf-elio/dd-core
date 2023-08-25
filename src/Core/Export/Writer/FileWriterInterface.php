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


use Elio\ElioSearch\Core\Export\ExportEntity;
use Elio\ElioSearch\Core\Export\ExportItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface FileWriterInterface
 * @package Elio\ElioSearch\Core\Export\Writer
 */
interface FileWriterInterface
{
    /**
     * Checks if the writer can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export) : bool;

    /**
     * Opens a new file handle that is used to write the export in
     * @return resource
     */
    public function open(SalesChannelContext $context);

    /**
     * Registers the model of the item that are written
     *
     * @param array $model
     */
    public function registerModel(array $model) : void;

    /**
     * @param resource $handle
     * @param ExportItem[] $items
     */
    public function writeList($handle, array $items) : void;

    /**
     * Closes the export and finalizes the file
     *
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     * @param resource $handle
     * @return void
     */
    public function close(ExportEntity $export, SalesChannelContext $context, $handle) : void;

    /**
     * Abort the write process because of an error
     * @param resource $handle
     * @return void
     */
    public function abort($handle) : void;
}