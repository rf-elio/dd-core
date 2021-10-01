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

namespace Elio\FactFinder\Api\Search\ResponseTransformer;

use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Elio\FactFinder\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationCollectionExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\Result;

/**
 * Class CampaignTransformer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignTransformer implements ResponseTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function supports(ModelInterface $model, SalesChannelContext $context): bool
    {
        return $model instanceof Result;
    }

    /**
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     */
    public function transform(ModelInterface $model, ResponseCollection $responseCollection, SalesChannelContext $context): void
    {
        if (!$model instanceof Result) {
            throw new InvalidTypeException($model, Result::class);
        }

        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
        $responseCollection->set(ProductListingResponse::class, $listing);

        $aggregationResultCollection = $listing->getAggregations() ?? new AggregationResultCollection();
        $listing->setAggregations($aggregationResultCollection);

        $struct = new AggregationCollectionExtension();
        $aggregationResultCollection->addExtension('ff-campaigns', $struct);

        foreach ($model->getCampaigns() as $campaign) {
            $type = $campaign->getFlavour();
            $label = $campaign->getName();
            $texts = [];
            foreach ($campaign->getFeedbackTexts() as $feedbackText){
                if(empty($texts[$feedbackText->getLabel()])){
                    $texts[$feedbackText->getLabel()] = [];
                }
                $texts[$feedbackText->getLabel()][] = [
                    'html' => $feedbackText->getHtml(),
                    'label' => $feedbackText->getLabel(),
                    'position' => $feedbackText->getPosition(),
                    'text' => $feedbackText->getText(),
                ];
            }
            $struct->addCampaign($type, [
                'type' => $type,
                'label' => $label,
                'texts' => $texts,
            ]);
        }
    }
}