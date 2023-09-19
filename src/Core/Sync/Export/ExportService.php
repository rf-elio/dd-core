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

use Elio\ElioSearch\Core\Sync\Collectors\DataCollectorInterface;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ExportService
{
    public function __construct(
        private readonly iterable $converters,
        private readonly iterable $collectors,
        private readonly iterable $writers
    )
    {
    }

    public function export(SyncProfileInterface $profile, SyncProfileEntity $syncProfile, SalesChannelContext $context): void
    {
        $converter = $this->getConverter($profile->getConverter());
        $writer = $this->getWriter($syncProfile);
        $collectors = $this->getCollectors($syncProfile->getDataType());
        $stream = new OutputStream($writer, $syncProfile, $context);
        $stream->open($context);
        foreach ($collectors as $collector) {
            $collection = $collector->collect($context);
            foreach ($collection as $entities) {
                foreach ($entities as $entity) {
                    $exportItem = $converter->conver($entity, $context);
                    $stream->write($exportItem);
                }
            }
        }

        $stream->close();
    }

    protected function getConverter(string $converterClass)
    {
        foreach ($this->converters as $converter) {
            if ($converter instanceof $converterClass) {
                return $converter;
            }
        }

        throw new \InvalidArgumentException(sprintf('Converter %s not found', $converterClass));
    }

    /**
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
            throw new \InvalidArgumentException('Collectors are not found');
        }

        return $collectors;
    }

    protected function getWriter(SyncProfileEntity $syncProfile)
    {
        foreach ($this->writers as $writer) {
            if($writer->supports($syncProfile->getFormat())) {
                return $writer;
            }
        }

        throw new ExportNotSupportedException(sprintf(
            'Export "%s" with format "%s" is not supported by any of the registered file writers',
            $syncProfile->getName(), $syncProfile->getFormat()
        ));
    }
}