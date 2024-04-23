<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Sync\Output\Message;

use Elio\ElioDataDiscovery\Core\Sync\AbstractDataCollection;
use Elio\ElioDataDiscovery\Core\Sync\SyncContext;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Class AsyncOutputMessage
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class AsyncOutputMessage implements AsyncMessageInterface
{
    public function __construct(
        protected readonly SyncContext $syncContext,
        protected readonly Context     $context,
        protected readonly string      $executionRecordId,
        protected readonly string      $dataCollectionSerialized
    )
    {
    }

    public static function create(
        SyncContext            $syncContext,
        Context                $context,
        string                 $executionRecordId,
        AbstractDataCollection $dataCollection
    ): AsyncOutputMessage
    {
        return new AsyncOutputMessage(
            $syncContext,
            $context,
            $executionRecordId,
            base64_encode(serialize($dataCollection))
        );
    }

    public function getSyncContext(): SyncContext
    {
        return $this->syncContext;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getDataCollectionSerialized(): string
    {
        return $this->dataCollectionSerialized;
    }

    #[Ignore]
    public function getDataCollection(): AbstractDataCollection
    {
        return unserialize(base64_decode($this->dataCollectionSerialized));
    }

    public function getExecutionRecordId(): string
    {
        return $this->executionRecordId;
    }
}