<?php

namespace Elio\FactFinder\Core\Export;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\ImportExport\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function is_resource;

class ExportStorageService
{
    private const BASE_DIR = 'ff-export';
    private FilesystemInterface $fileSystem;

    /**
     * CSVFileWriter constructor.
     * @param FilesystemInterface $fileSystem
     */
    public function __construct(FilesystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Creates the file name based on the export and sales channel
     *
     * @param ExportEntity $export
     * @return string
     */
    public function createFileName(ExportEntity $export): string
    {
        return sprintf(
            '%s/%s-%s.%s',
            self::BASE_DIR,
            $export->getId(),
            $export->getName(),
            $export->getFormat()
        );
    }

    /**
     * Checks if the export exists
     *
     * @param ExportEntity $export
     * @return bool
     */
    public function exists(ExportEntity $export): bool
    {
        $fileName = $this->createFileName($export);
        return $this->fileSystem->has($fileName);
    }

    /**
     * Creates a file response for the given export
     *
     * @param ExportEntity $export
     * @return Response
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function createFileResponse(ExportEntity $export): Response
    {
        if (!$this->exists($export)) {
            throw new FileNotFoundException($export->getId());
        }

        $fileName = $this->createFileName($export);
        $headers = [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                'attachment',
                $export->getName().'.'.$export->getFormat(),
                // only printable ascii
                preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $export->getName().'.'.$export->getFormat())
            ),
            'Content-Length' => $this->fileSystem->getSize($fileName),
            'Content-Type' => 'application/octet-stream',
        ];

        $stream = $this->fileSystem->readStream($fileName);
        if (!is_resource($stream)) {
            throw new FileNotFoundException($export->getId());
        }

        return new StreamedResponse(function () use ($stream): void {
            fpassthru($stream);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Writes the content of the given resource into the export file
     *
     * @param ExportEntity $export
     * @param resource $handle
     * @throws FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function write(ExportEntity $export, $handle): void
    {
        $this->fileSystem->createDir(self::BASE_DIR);
        $fileName = $this->createFileName($export);

        if($this->fileSystem->has($fileName)) {
            $this->fileSystem->delete($fileName);
        }

        $this->fileSystem->writeStream($fileName, $handle);
    }
}