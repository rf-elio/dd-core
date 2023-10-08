<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioSearch\Core\Sync\Export;

use Elio\ElioSearch\Core\Sync\Collector\DataCollectorInterface;
use Elio\ElioSearch\Core\Sync\Export\Converter\ConverterInterface;
use Elio\ElioSearch\Core\Sync\Export\Event\ExportGeneratedEvent;
use Elio\ElioSearch\Core\Sync\Export\Exception\ExportNotSupportedException;
use Elio\ElioSearch\Core\Sync\Export\Writer\FileWriterInterface;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ExportService
 * @package Elio\ElioSearch\Core\Sync\Export
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ExportService
{
    public function __construct(
        private readonly iterable $converters,
        private readonly iterable $collectors,
        private readonly iterable $writers,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * Exports configured profile data to file
     *
     * @param SyncProfileInterface $profile
     * @param SyncProfileEntity $syncProfile
     * @param SalesChannelContext $context
     * @return void
     */
    public function export(SyncProfileInterface $profile, SyncProfileEntity $syncProfile, SalesChannelContext $context): void
    {
        $converter = $this->getConverter($syncProfile, $profile->getConverters());
        $writer = $this->getWriter($syncProfile);
        $collectors = $this->getCollectors($syncProfile->getDataType());
        $stream = new OutputStream($writer, $syncProfile, $context);
        $stream->open($context);
        foreach ($collectors as $collector) {
            $collection = $collector->collect($syncProfile->getLanguages()->getIds(), $context);
            $currentItems = $collection->current() ?? [];
            foreach ($currentItems as $entities) {
                $exportItem = $converter->convert($entities, $syncProfile, $context);
                $stream->write($exportItem);
            }
        }

        $stream->close();
        $this->eventDispatcher->dispatch(new ExportGeneratedEvent($syncProfile, $context));
    }

    /**
     * Gets profile converter
     *
     * @param SyncProfileEntity $syncProfile
     * @param array $converters
     * @return ConverterInterface
     */
    protected function getConverter(SyncProfileEntity $syncProfile, array $converters): ConverterInterface
    {
        if (!isset($converters[$syncProfile->getDataType()])) {
            throw new InvalidArgumentException(sprintf('Converter for data type %s not found', $syncProfile->getDataType()));
        }

        $converterClass = $converters[$syncProfile->getDataType()];
        foreach ($this->converters as $converter) {
            if ($converter instanceof $converterClass) {
                return $converter;
            }
        }

        throw new InvalidArgumentException(sprintf('Converter %s not found', $converterClass));
    }

    /**
     * Gets profile collector
     *
     * @param string $dataType
     * @return DataCollectorInterface[]
     */
    protected function getCollectors(string $dataType): array
    {
        $collectors = [];
        /** @var DataCollectorInterface $collector */
        foreach ($this->collectors as $collector) {
            if ($collector->supports($dataType)) {
                $collectors[] = $collector;
            }
        }

        if (empty($collectors)) {
            throw new InvalidArgumentException('Collectors are not found');
        }

        return $collectors;
    }

    /**
     * Gets profile writer
     *
     * @param SyncProfileEntity $syncProfile
     * @return FileWriterInterface
     */
    protected function getWriter(SyncProfileEntity $syncProfile): FileWriterInterface
    {
        foreach ($this->writers as $writer) {
            if($writer->supports($syncProfile->getOutput())) {
                return $writer;
            }
        }

        throw new ExportNotSupportedException(sprintf(
            'Export "%s" with format "%s" is not supported by any of the registered file writers',
            $syncProfile->getName(), $syncProfile->getOutput()
        ));
    }
}