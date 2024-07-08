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

namespace Elio\ElioDataDiscovery\Core\Logging;


use Elio\ElioDataDiscovery\Core\Logging\Handler\ElioDataDiscoveryFilterHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerFactory
 * @package Elio\ElioDataDiscovery\Core\Logging
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class LoggerFactory
{
    /**
     * LoggerFactory constructor.
     * @param string $rotatingFilePathPattern
     * @param int $defaultFileRotationCount
     * @param LogFilterContext $logFilterContext
     */
    public function __construct(
        private readonly string $rotatingFilePathPattern,
        private readonly int $defaultFileRotationCount,
        private readonly LogFilterContext $logFilterContext,
        private readonly AbstractProcessingHandler $consoleHandler
    ) {}

    /**
     * Creates the logger with additional filtering
     *
     * @param string $filePrefix
     * @param int|null $fileRotationCount
     * @param bool $useLogFilter
     * @param bool $useJsonFormatter
     * @param Level $loggerLevel
     * @return LoggerInterface
     */
    public function createRotating(
        string $filePrefix,
        ?int $fileRotationCount = null,
        bool $useLogFilter = false,
        bool $useJsonFormatter = true,
        Level $loggerLevel = Level::Debug
    ): LoggerInterface
    {
        $filepath = sprintf($this->rotatingFilePathPattern, $filePrefix);

        $result = new Logger($filePrefix);
        $handler = new RotatingFileHandler($filepath, $fileRotationCount ?? $this->defaultFileRotationCount, $loggerLevel);

        if ($useJsonFormatter) {
            $formatter = new JsonFormatter();
            $formatter->setJsonPrettyPrint(true);
            $handler->setFormatter($formatter);
        }

        $formatter = new JsonFormatter();
        $formatter->setJsonPrettyPrint(true);
        $handler->setFormatter($formatter);
        $result->pushHandler($this->consoleHandler);

        if ($useLogFilter) {
            $result->pushHandler(new ElioDataDiscoveryFilterHandler($handler, $this->logFilterContext));
        } else {
            $result->pushHandler($handler);
        }

        $result->pushProcessor(new PsrLogMessageProcessor());

        return $result;
    }
}
