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

namespace Elio\FactFinder\Api\Import\ResponseTransformer;


use Elio\FactFinder\Api\Import\Response\ImportResponse;
use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ImportChannelResult;
use Swagger\Client\Model\ModelInterface;

/**
 * Class ImportTransformer
 * @package Elio\FactFinder\Api\Import\ResponseTransformer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ImportTransformer implements ResponseTransformerInterface
{
    /**
     * All results that are type of import channel can be transformed
     *
     * @param ModelInterface $model
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     * @return bool
     */
    public function supports(ModelInterface $model, ApiRequest $request, SalesChannelContext $context): bool
    {
        return $model instanceof ImportChannelResult;
    }

    /**
     * Converts the api model to an response object
     *
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(ModelInterface $model, ResponseCollection $responseCollection, SalesChannelContext $context, ApiRequest $request): void
    {
        if (!$model instanceof ImportChannelResult) {
            throw new InvalidTypeException($model, ImportChannelResult::class);
        }

        $response = new ImportResponse(
            $model->getChannel(),
            $model->getDurationInSeconds(),
            $model->getErrorMessages(),
            $model->getImportType(),
            $model->getImportedFields() ?? 0,
            $model->getImportedRecords() ?? 0,
            $model->getImportedWorldmatchDocuments() ?? 0,
            $model->getStartDate(),
            $model->getStatusMessages()
        );

        $responseCollection->set(ImportResponse::class, $response);
    }
}
