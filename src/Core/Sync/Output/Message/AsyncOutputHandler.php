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

use Doctrine\DBAL\Exception;
use Elio\ElioDataDiscovery\Core\Sync\Output\OutputService;
use Elio\ElioDataDiscovery\Core\Sync\SyncService;
use Elio\ElioDataDiscovery\Core\Sync\SyncStatusService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class AsyncOutputHandler
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
#[AsMessageHandler]
class AsyncOutputHandler
{
    public const SUPPORTS_ASYNC_FEATURE = 'asyncOutputSupport';

    public function __construct(
        private readonly OutputService $outputService,
        private readonly SyncService $syncService,
        private readonly SyncStatusService $syncStatusService
    ) {}

    /**
     * @param AsyncOutputMessage $message
     * @throws Exception
     */
    public function __invoke(AsyncOutputMessage $message): void
    {
        $syncProfileExecutionEntity = $this->syncStatusService->getSyncProfileExecutionById(
            $message->getSyncProfileExecutionEntityId(),
            $message->getContext()
        );

        $sycProfileEntity = $this->syncService->getSyncProfileEntity(
            $message->getSyncProfileEntityId(),
            $message->getContext()
        );
        $syncContext = $this->syncService->createSyncContext($sycProfileEntity);
        $outputStream = $this->outputService->createOutputStream($syncContext);
        $outputStream->write($message->getDataCollection());
        $this->syncStatusService->increaseProcessedCount($syncProfileExecutionEntity);
        $this->syncStatusService->checkSyncProfileExecutionStatus(
            $syncProfileExecutionEntity,
            $outputStream,
            $syncContext,
            $message->getContext()
        );
    }

    /**
     * @return iterable<string>
     */
    public static function getHandledMessages(): iterable
    {
        return [
            AsyncOutputMessage::class
        ];
    }
}
