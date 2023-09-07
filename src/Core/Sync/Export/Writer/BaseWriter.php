<?php declare(strict_types=1);
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

namespace Elio\ElioSearch\Core\Sync\Export\Writer;

use Elio\ElioSearch\Core\Export\ExportItem;
use Elio\ElioSearch\Core\Export\ExportStorageService;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use League\Flysystem\FilesystemException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class BaseWriter
 * @package Elio\ElioSearch\Core\Sync\Export\Writer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseWriter
{
    private ExportStorageService $exportStorageService;
    protected array $model = [];

    /**
     * CSVFileWriter constructor.
     * @param ExportStorageService $exportStorageService
     */
    public function __construct(ExportStorageService $exportStorageService)
    {
        $this->exportStorageService = $exportStorageService;
    }

    /**
     * Opens a temporary file handle
     *
     * @return resource
     */
    public function open(SalesChannelContext $context)
    {
        return tmpfile();
    }

    /**
     * Registers the model of the item that are written
     *
     * @param array $model
     */
    public function registerModel(array $model) : void
    {
        $this->model = array_unique(array_merge($this->model, $model));
    }

    /**
     * @param resource $handle
     * @param array $items
     */
    public function writeList($handle, array $items): void
    {
        foreach ($items as $item) {
            $this->write($handle, $item);
        }
    }

    /**
     * Writes a single item
     *
     * @param resource $handle
     * @param ExportItem $item
     */
    abstract protected function write($handle, ExportItem $item) : void;

    /**
     * Closes the file handle
     *
     * @param resource $handle
     */
    public function abort($handle) : void
    {
        $this->model = [];
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    /**
     * @param SyncProfileEntity $syncProfile
     * @param SalesChannelContext $context
     * @param resource $handle
     * @throws FilesystemException
     */
    public function close(SyncProfileEntity $syncProfile, SalesChannelContext $context, $handle): void
    {
        $this->exportStorageService->write($syncProfile, $handle);
        $this->model = [];
        if (is_resource($handle)) {
            fclose($handle);
        }
    }
}