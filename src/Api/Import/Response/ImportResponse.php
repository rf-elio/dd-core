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

namespace Elio\FactFinder\Api\Import\Response;


use DateTimeInterface;
use Elio\FactFinder\Api\Response\Response;

/**
 * Class ImportResponse
 * @package Elio\FactFinder\Api\Import\Response
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ImportResponse extends Response
{
    private string $channel;
    private int $durationInSeconds;
    private array $errorMessages;
    private string $importType;
    private int $importedFields;
    private int $importedRecords;
    private int $importedWorldMatchDocuments;
    private DateTimeInterface $startTime;
    private array $statusMessages;

    /**
     * ImportResponse constructor.
     * @param string $channel
     * @param int $durationInSeconds
     * @param array $errorMessages
     * @param string $importType
     * @param int $importedFields
     * @param int $importedRecords
     * @param int $importedWorldMatchDocuments
     * @param DateTimeInterface $startTime
     * @param array $statusMessages
     */
    public function __construct(
        string $channel,
        int $durationInSeconds,
        array $errorMessages,
        string $importType,
        int $importedFields,
        int $importedRecords,
        int $importedWorldMatchDocuments,
        DateTimeInterface $startTime,
        array $statusMessages
    )
    {
        $this->channel = $channel;
        $this->durationInSeconds = $durationInSeconds;
        $this->errorMessages = $errorMessages;
        $this->importType = $importType;
        $this->importedFields = $importedFields;
        $this->importedRecords = $importedRecords;
        $this->importedWorldMatchDocuments = $importedWorldMatchDocuments;
        $this->startTime = $startTime;
        $this->statusMessages = $statusMessages;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getDurationInSeconds(): int
    {
        return $this->durationInSeconds;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @return string
     */
    public function getImportType(): string
    {
        return $this->importType;
    }

    /**
     * @return int
     */
    public function getImportedFields(): int
    {
        return $this->importedFields;
    }

    /**
     * @return int
     */
    public function getImportedRecords(): int
    {
        return $this->importedRecords;
    }

    /**
     * @return int
     */
    public function getImportedWorldMatchDocuments(): int
    {
        return $this->importedWorldMatchDocuments;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartTime(): DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @return array
     */
    public function getStatusMessages(): array
    {
        return $this->statusMessages;
    }
}