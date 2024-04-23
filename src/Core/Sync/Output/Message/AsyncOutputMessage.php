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
use Elio\ElioDataDiscovery\Core\Sync\SyncProfileEntity;
use Elio\ElioDataDiscovery\Core\Sync\SyncProfileExecutionEntity;
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
        protected readonly string $syncProfileEntityId,
        protected readonly Context $context,
        protected readonly string $syncProfileExecutionEntityId,
        protected readonly string $dataCollectionSerialized
    ) {
    }

    public static function create(
        string $syncProfileEntityId,
        Context $context,
        SyncProfileExecutionEntity $syncProfileExecutionEntity,
        AbstractDataCollection $dataCollection
    ): AsyncOutputMessage {
        return new AsyncOutputMessage(
            $syncProfileEntityId,
            $context,
            $syncProfileExecutionEntity->getId(),
            base64_encode(gzencode(serialize($dataCollection)))
        );
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
        return unserialize(
            gzdecode(base64_decode($this->dataCollectionSerialized)),
            ['allowed_classes' => true]
        );
    }

    public function getSyncProfileExecutionEntityId(): string
    {
        return $this->syncProfileExecutionEntityId;
    }

    public function getSyncProfileEntityId(): string
    {
        return $this->syncProfileEntityId;
    }
}
