<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Sync\Output\File;

use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Content\ImportExport\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function is_resource;

/**
 * Class ExportStorageService
 * @package Elio\ElioSearch\Core\Sync\Output\File
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ExportStorageService
{
    private const BASE_DIR = 'elio-search-export';
    private FilesystemOperator $fileSystem;

    /**
     * CSVFileWriter constructor.
     * @param FilesystemOperator $fileSystem
     */
    public function __construct(FilesystemOperator $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Creates the file name based on the export and sales channel
     *
     * @param SyncProfileEntity $syncProfile
     * @return string
     */
    public function createFileName(SyncProfileEntity $syncProfile): string
    {
        return sprintf(
            '%s/%s-%s.%s',
            self::BASE_DIR,
            $syncProfile->getId(),
            $syncProfile->getName(),
            $syncProfile->getOutput()
        );
    }

    /**
     * Checks if the export exists
     *
     * @param SyncProfileEntity $syncProfile
     * @return bool
     * @throws FilesystemException
     */
    public function exists(SyncProfileEntity $syncProfile): bool
    {
        $fileName = $this->createFileName($syncProfile);
        return $this->fileSystem->has($fileName);
    }

    /**
     * Creates a file response for the given export
     *
     * @param SyncProfileEntity $syncProfile
     * @return Response
     * @throws FilesystemException
     */
    public function createFileResponse(SyncProfileEntity $syncProfile): Response
    {
        if (!$this->exists($syncProfile)) {
            throw new FileNotFoundException($syncProfile->getId());
        }

        $fileName = $this->createFileName($syncProfile);
        $headers = [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                'attachment',
                $syncProfile->getName().'.'.$syncProfile->getOutput(),
                // only printable ascii
                preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $syncProfile->getName().'.'.$syncProfile->getOutput())
            ),
            'Content-Length' => $this->fileSystem->fileSize($fileName),
            'Content-Type' => 'application/octet-stream',
        ];

        $stream = $this->fileSystem->readStream($fileName);
        if (!is_resource($stream)) {
            throw new FileNotFoundException($syncProfile->getId());
        }

        return new StreamedResponse(function () use ($stream): void {
            fpassthru($stream);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Writes the content of the given resource into the export file
     *
     * @param SyncProfileEntity $syncProfile
     * @param resource $handle
     * @throws FilesystemException
     */
    public function write(SyncProfileEntity $syncProfile, $handle): void
    {
        $this->fileSystem->createDirectory(self::BASE_DIR);
        $fileName = $this->createFileName($syncProfile);

        if($this->fileSystem->has($fileName)) {
            $this->fileSystem->delete($fileName);
        }

        $this->fileSystem->writeStream($fileName, $handle);
    }
}