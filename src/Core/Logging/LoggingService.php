<?php


namespace Elio\ElioSearch\Core\Logging;


use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class LoggingService
 *
 * @package Elio\ElioSearch\Core\Logging
 */
class LoggingService implements LoggingServiceInterface
{
    public const FILE_NAME = 'elio_search_finder';
    public const LOG_FORMAT = <<<EOT
    {
        method: {method}
        uri: {uri}
        req_body: {req_body}
        res_body: {res_body}
    }
EOT;
    private readonly Finder $finder;
    private array $logs = [];

    /**
     * LoggingService constructor.
     *
     * @param string $logDir
     */
    public function __construct(
        private readonly string $logDir
    )
    {
        $this->finder = new Finder();
        $this->fillLogs();
    }

    /**
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @param int $index
     *
     * @return array
     */
    public function getLogContents(int $index): array
    {
        $content = $this->getLogContent($index);
        if (empty($content)) {
            return [];
        }

        return $this->contentToArray($content);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getLogContent(int $index): string
    {
        $content = file_get_contents($this->getFilepath($index));
        if (!$content) {
            return '';
        }
        return $this->prepareContent($content);
    }

    /**
     * @param int $index
     */
    public function delete(int $index): void
    {
        $filePath = $this->getFilepath($index);
        unlink($filePath);
    }

    /**
     * @param string $content
     *
     * @return array
     */
    private function contentToArray(string $content): array
    {
        $content = preg_replace('/<br>\}<br>\{<br>/', '<br>}###{<br>', $content);
        return array_reverse(explode('###', (string) $content));
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function prepareContent(string $content): string
    {
        $prepared = htmlspecialchars($content);
        $prepared = str_replace(["\n", '\n'], '<br>', $prepared);
        return preg_replace('/\s/', '&nbsp;', $prepared) ?? '';
    }

    /**
     * Collects all logs created by elio search
     */
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

    /**
     * @param int $index
     *
     * @return string
     */
    private function getFilepath(int $index): string
    {
        if (!isset($this->logs[$index])) {
            throw new RuntimeException(sprintf('Log with index %s does not exist', $index));
        }

        $filePath = $this->logDir . '/' . $this->logs[$index];
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('Log %s does not exist"', $index));
        }

        return $filePath;
    }
}
