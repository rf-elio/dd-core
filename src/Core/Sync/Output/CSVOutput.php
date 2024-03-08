<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioSearch\Core\Sync\Output;

use Elio\ElioSearch\Core\Sync\DataTypes\DataTypeInterface;
use Elio\ElioSearch\Core\Sync\SyncContext;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Struct\Collection;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

/**
 * Class CSVOutput
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
abstract class CSVOutput implements OutputInterface, WriteAwareInterface, HandleInterface
{
    public const TYPE = self::class;
    private const SEPARATOR = ';';
    private bool $headerWritten = false;
    /**
     * @var resource
     */
    private $fileHandle;

    /**
     * @var SyncProfileEntity|null
     */
    private ?SyncProfileEntity $syncProfile = null;

    public function __construct(
        private readonly FilesystemOperator $fileSystem,
        private readonly LoggerInterface    $logger
    ) {}

    abstract protected function getType(): string;

    abstract protected function getBaseDir(): string;

    abstract protected function getModel(DataTypeInterface $exportItem, SyncContext $syncContext): array;

    /**
     * Must be implemented in extension plugin
     *
     * @param DataTypeInterface $dataType
     * @param SyncContext $syncContext
     * @return array
     */
    abstract protected function prepare(DataTypeInterface $exportItem, SyncContext $syncContext): array;

    /**
     * @param Collection $collection
     * @param SyncContext $syncContext
     * @return void
     */
    public function write(Collection $collection, SyncContext $syncContext): void
    {
        if (!$this->headerWritten) {
            fputcsv($this->fileHandle, $this->getModel($collection->first(), $syncContext), self::SEPARATOR);
            $this->headerWritten = true;
        }

        foreach ($collection as $item) {
            $row = $this->prepare($item, $syncContext);
            fputcsv($this->fileHandle, $row, self::SEPARATOR);
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $this->getType() === $type;
    }

    /**
     * Opens a temporary file handle
     *
     * @param SyncContext $syncContext
     * @return void
     */
    public function open(SyncContext $syncContext): void
    {
        $this->syncProfile = $syncContext->getSyncProfile();
        $this->headerWritten = false;
        $this->fileHandle = tmpfile();
    }

    /**
     * Writes the content of the given resource into the export file
     *
     * @throws FilesystemException
     */
    public function close(): void
    {
        $this->fileSystem->createDirectory($this->getBaseDir());

        if ($this->syncProfile) {
            $fileName = $this->createFileName($this->syncProfile);

            if ($this->fileSystem->has($fileName)) {
                $this->fileSystem->delete($fileName);
            }

            $this->fileSystem->writeStream($fileName, $this->fileHandle);
        }

        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }

    /**
     * Creates the file name based on the export and sales channel
     *
     * @param SyncProfileEntity $export
     * @return string
     */
    public function createFileName(SyncProfileEntity $export): string
    {
        return sprintf(
            '%s/%s-%s.csv',
            $this->getBaseDir(),
            $export->getId(),
            $export->getName()
        );
    }

    /**
     * Checks if the export exists
     *
     * @param SyncProfileEntity $export
     * @return bool
     * @throws FilesystemException
     */
    public function exists(SyncProfileEntity $export): bool
    {
        $fileName = $this->createFileName($export);
        return $this->fileSystem->has($fileName);
    }
}