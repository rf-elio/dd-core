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

use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Response\AdvisorCampaignResponse;
use Elio\FactFinder\Api\Search\Response\AdvisorCampaignResponseCollection;
use Elio\FactFinder\Api\Search\Response\CampaignFeedbackResponse;
use Elio\FactFinder\Api\Search\Response\CampaignFeedbackResponseCollection;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Search\Response\CampaignRedirectionResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\AdvisorCampaign\AdvisorAnswer;
use Elio\FactFinder\Core\AdvisorCampaign\AdvisorQuestion;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Elio\FactFinder\Core\Util\Tree\RandomAddTree;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\Question;
use Swagger\Client\Model\Result;

/**
 * Converts the campaigns to the internal campaign objects
 *
 * Class CampaignTransformer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignTransformer implements ResponseTransformerInterface
{
    private CONST FLAVOR_REDIRECT = 'REDIRECT';
    private CONST FLAVOR_ADVISOR = 'ADVISOR';

    /**
     * @inheritDoc
     */
    public function supports(ModelInterface $model, ApiRequest $request, SalesChannelContext $context): bool
    {
        return $model instanceof Result;
    }

    /**
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(
        ModelInterface $model,
        ResponseCollection $responseCollection,
        SalesChannelContext $context,
        ApiRequest $request
    ): void
    {
        if (!$model instanceof Result) {
            throw new InvalidTypeException($model, Result::class);
        }

        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
        $responseCollection->set(ProductListingResponse::class, $listing);

        $campaignFeedbackResponseCollection = new CampaignFeedbackResponseCollection();
        $responseCollection->set(CampaignFeedbackResponseCollection::KEY, $campaignFeedbackResponseCollection);

        $advisorCampaignResponseCollection = new AdvisorCampaignResponseCollection();
        $responseCollection->set(AdvisorCampaignResponseCollection::KEY, $advisorCampaignResponseCollection);

        foreach ($model->getCampaigns() as $campaign) {
            if ($campaign->getFlavour() === self::FLAVOR_REDIRECT) {
                $responseCollection->set(CampaignRedirectionResponse::class, new CampaignRedirectionResponse(
                    $campaign->getTarget()->getName(),
                    $campaign->getTarget()->getDestination(),
                ));
            }

            foreach ($campaign->getFeedbackTexts() as $feedbackText){
                $campaignFeedbackResponseCollection->addCampaignFeedbackResponse(new CampaignFeedbackResponse(
                    $feedbackText->getLabel(),
                    $feedbackText->getText(),
                    $feedbackText->getHtml()
                ));
            }

            if ($campaign->getFlavour() === self::FLAVOR_ADVISOR) {
                $questions = [];
                foreach ($campaign->getAdvisorTree() as $question) {
                    $questions[] = $this->questionWalk($question);
                }

                $advisorCampaignResponseCollection->addAdvisorCampaignResponse(new AdvisorCampaignResponse(
                    $campaign->getId(),
                    $campaign->getName(),
                    $questions
                ));
            }
        }
    }

    private function questionWalk(Question $question): AdvisorQuestion
    {
        $advisorQuestion = (new AdvisorQuestion())
            ->setId($question->getId())
            ->setText($question->getText())
            ->setVisible($question->getVisible());

        foreach ($question->getAnswers() as $answer) {
            $advisorAnswer = (new AdvisorAnswer())
                ->setId($answer->getId())
                ->setText($answer->getText())
                ->setSelected($answer->getSelected());

            foreach ($answer->getQuestions() as $subQuestion) {
                $advisorAnswer->addQuestion($this->questionWalk($subQuestion));
            }

            $advisorQuestion->addAnswer($advisorAnswer);
        }

        return $advisorQuestion;
    }
}
