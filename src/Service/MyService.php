<?php declare(strict_types=1);

namespace Elio\ElioFactFinder\Service;
use League\Flysystem\FilesystemInterface;

class MyService
{

    /** @var string */
    private $exportDirectory;

    /** @var FilesystemInterface */
    private $fileSystem;

    public function __construct(string $exportDirectory, FilesystemInterface $filesystem) {
        $this->exportDirectory = $exportDirectory;
        $this->fileSystem = $filesystem;
    }
    public function getExportDirectory(): string
    {
        return $this->exportDirectory;
    }

    public function getFileSystem(): FilesystemInterface
    {
        return $this->fileSystem;
    }
}
