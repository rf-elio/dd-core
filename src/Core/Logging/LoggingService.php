<?php


namespace Elio\FactFinder\Core\Logging;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class LoggingService
 *
 * @package Elio\FactFinder\Core\Logging
 */
class LoggingService
{
    public const FILE_NAME = 'elio_fact_finder-api-client';

    private string $logDir;
    private Finder $finder;
    private array $logs = [];

    /**
     * LoggingService constructor.
     *
     * @param string $logDir
     */
    public function __construct(
        string $logDir
    )
    {
        $this->logDir = $logDir;
        $this->finder = new Finder();

        $this->fillLogs();
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getLogContent(string $log): string
    {
        return file_get_contents($this->logDir . '/' . $this->logs[$log]);
    }

    private function fillLogs(): void
    {
        $files = $this->finder
            ->files()
            ->in($this->logDir)
            ->reverseSorting()
            ->sortByName()
            ->name(self::FILE_NAME . '*');

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $this->logs[] = $file->getFilename();
        }
    }
}
