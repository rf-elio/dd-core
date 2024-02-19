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

namespace Elio\ElioSearch\Core\Sync\Input;

use Elio\ElioSearch\Core\Sync\SyncContext;
use Elio\ElioSearch\Core\Sync\FullDataCollection;
use Elio\ElioSearch\ElioSearch;
use Generator;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Elio\ElioSearch\Core\Sync\DataTypes\ProductDataType;
use Elio\ElioSearch\Core\Sync\DataTypes\ContentDataType;
use Elio\ElioSearch\Core\Sync\DataTypes\Exception\UnknownContentTypeException;

/**
 * Class FullInput
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class FullInput extends BaseInput
{
    public const TYPE = self::class;

    public function __construct(
        private readonly iterable        $collectors,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct($collectors);
    }

    /**
     * Checks if writer is supported
     *
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }

    /**
     * @param SyncContext $syncContext
     * @return Generator<FullDataCollection>
     */
    public function read(SyncContext $syncContext): Generator
    {
        $syncProfile = $syncContext->getSyncProfile();
        $contexts = $syncContext->getSalesChannelContexts();

        $this->logger->info('FullInput: DataType', [
            'type' => $syncProfile->getDataType()
        ]);

        foreach ($this->getCollectors($syncProfile->getDataType()) as $collector) {
            foreach ($collector->collect($contexts) as $collection) {
                yield new FullDataCollection($syncProfile->getDataType(), $collection);
            }
        }
    }
}