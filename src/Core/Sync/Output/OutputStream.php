<?php
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

namespace Elio\ElioSearch\Core\Sync\Output;


use Elio\ElioSearch\Core\Sync\AbstractDataCollection;
use Elio\ElioSearch\Core\Sync\DeltaDataCollection;
use Elio\ElioSearch\Core\Sync\SyncContext;
use Shopware\Core\Framework\Struct\Collection;

/**
 * Class OutputStream
 * @package Elio\ElioSearch\Core\Sync\Output
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class OutputStream
{
    /**
     * @param OutputInterface[] $outputs
     */
    public function __construct(
        private readonly iterable $outputs,
        private readonly SyncContext $syncContext
    ) {}

    public function open(): void
    {
        foreach ($this->outputs as $output) {
            if ($output instanceof HandleInterface) {
                $output->open($this->syncContext);
            }
        }
    }

    public function close(): void
    {
        foreach ($this->outputs as $output) {
            if ($output instanceof HandleInterface) {
                $output->close();
            }
        }
    }

    public function write(Collection $dataCollection): void
    {
        foreach ($this->outputs as $output) {
            if ($output instanceof DeltaAwareInterface) {
                /** @var AbstractDataCollection $dataCollection */
                if ($dataCollection->getType() === DeltaDataCollection::TYPE_CREATED) {
                    $output->create($dataCollection, $this->syncContext);
                } elseif ($dataCollection->getType() === DeltaDataCollection::TYPE_UPDATED) {
                    $output->update($dataCollection, $this->syncContext);
                } elseif ($dataCollection->getType() === DeltaDataCollection::TYPE_DELETED) {
                    $output->delete($dataCollection, $this->syncContext);
                }
            } elseif ($output instanceof WriteAwareInterface) {
                $output->write($dataCollection, $this->syncContext);
            } else {
                throw new \RuntimeException('Output does not support writing');
            }
        }
    }
}