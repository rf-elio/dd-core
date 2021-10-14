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

namespace Elio\FactFinder\Api\Transform;


use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Transform\Event\TransformResponseEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ModelInterface;
use Throwable;

/**
 * Class Transformer
 * @package Elio\FactFinder\Api\Transform
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class Transformer
{
    /**
     * @var iterable|ResponseTransformerInterface[]
     */
    private iterable $responseTransformer;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    /**
     * Transformer constructor.
     * @param iterable|ResponseTransformerInterface[] $responseTransformer
     */
    public function __construct(
        iterable $responseTransformer,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    )
    {
        $this->responseTransformer = $responseTransformer;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Transforms the ff response to an response that is supported by shopware
     *
     * @param ModelInterface $model
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     * @return ResponseCollection
     * @throws Throwable
     */
    public function transformResponse(ModelInterface $model, SalesChannelContext $context, ApiRequest $request) : ResponseCollection
    {
        $collection = new ResponseCollection();

        foreach ($this->responseTransformer as $responseTransformer) {
            try {
                if ($responseTransformer->supports($model, $context)) {
                    $responseTransformer->transform($model, $collection, $context, $request);
                }
            }
            catch (Throwable $ex) {
                $this->logger->error('Response transformer caused an error during transform', [
                    'message' => $ex->getMessage(),
                    'transformer' => get_class($responseTransformer),
                    'model' => get_class($model)
                ]);
                throw $ex;
            }
        }

        $this->eventDispatcher->dispatch(new TransformResponseEvent($model, $collection));
        return $collection;
    }
}