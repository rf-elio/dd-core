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
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class BaseWriter
 * @package Elio\FactFinder\Core\Export\Writer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseWriter
{
    private const BASE_DIR = 'ff-export';
    protected FilesystemInterface $fileSystem;

    /**
     * CSVFileWriter constructor.
     * @param FilesystemInterface $fileSystem
     */
    public function __construct(FilesystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Opens a temporary file handle
     *
     * @return false|resource
     */
    public function open()
    {
        return tmpfile();
    }

    /**
     * Closes the file handle
     *
     * @param resource $handle
     */
    public function abort($handle) : void
    {
        fclose($handle);
    }

    /**
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     * @param resource $handle
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function close(ExportEntity $export, SalesChannelContext $context, $handle): void
    {
        $this->fileSystem->createDir(self::BASE_DIR);
        $fileName = $this->createFileName($export, $context);

        if($this->fileSystem->has($fileName)) {
            $this->fileSystem->delete($fileName);
        }

        $this->fileSystem->writeStream($fileName, $handle);
        fclose($handle);
    }

    /**
     * Creates the file name based on the export and sales channel
     *
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     * @return string
     */
    protected function createFileName(ExportEntity $export, SalesChannelContext $context) : string
    {
        return sprintf(
            '%s/e%s-s%s-l%s.%s',
            self::BASE_DIR, $export->getId(), $context->getSalesChannelId(),
            $context->getSalesChannel()->getLanguageId(), $export->getFormat()
        );
    }
}