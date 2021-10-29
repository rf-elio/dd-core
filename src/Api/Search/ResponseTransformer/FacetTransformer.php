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
use Elio\FactFinder\Api\Search\Request\NavigationRequest;
use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Elio\FactFinder\Api\Search\Response\ProductListingResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Elio\FactFinder\Core\Framework\DataAbstractionLayer\Search\AggregationResult\FacetCollection;
use Elio\FactFinder\Core\Framework\DataAbstractionLayer\Search\AggregationResult\DefaultFacetExtension;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\StatsResult;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\Facet;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\Result;
use Elio\FactFinder\Core\FilterRestrictions\FilterService;

/**
 * Class FacetTransformer
 * @package Elio\FactFinder\Api\Search\ResponseTransformer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FacetTransformer implements ResponseTransformerInterface
{
    private FilterService $filterService;

    /**
     * ProductHandler constructor.
     * @param FilterService $filterService
     */
    public function __construct(
        FilterService $filterService
    ) {
        $this->filterService = $filterService;
    }

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
     * @param ApiRequest $request
     */
    public function transform(
        ModelInterface $model,
        ResponseCollection $responseCollection,
        SalesChannelContext $context,
        ApiRequest $request
    ): void {
        if (!$model instanceof Result) {
            throw new InvalidTypeException($model, Result::class);
        }

        $level = FilterService::LEVEL_GLOBAL;
        if ($request instanceof NavigationRequest) {
            $level = FilterService::LEVEL_CATEGORY;
        } else if ($request instanceof SearchRequest) {
            $level = FilterService::LEVEL_SEARCH;
        }

        $filtersRestrictions = $this->filterService->getFilters($context, $level, $request) ?? [null, []];
        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
        $responseCollection->set(ProductListingResponse::class, $listing);

        $aggregationResultCollection = $listing->getAggregations() ?? new AggregationResultCollection();
        $listing->setAggregations($aggregationResultCollection);

        $facetCollection = new FacetCollection('ff-default');
        $aggregationResultCollection->add($facetCollection);

        foreach ($model->getFacets() as $facet) {
            if ($filtersRestrictions[1] === null) { // blocked all
                continue;
            }

            if (($filtersRestrictions[0] !== null) && !in_array($facet->getName(), $filtersRestrictions[0], true)) {
                // isn't allowed
                continue;
            }

            $style = $facet->getFilterStyle();
            switch ($style) {
                case 'DEFAULT':
                    $defaultCollection = new PropertyGroupCollection();
                    $entity = $this->transformDefault($facet);
                    $defaultCollection->add($entity);
                    $facetCollection->addAggregation(
                        new EntityResult($facet->getName(), $defaultCollection),
                        $style
                    );
                    break;
                case 'SLIDER':
                    $facetCollection->addAggregation(
                        $this->transformSlider($facet),
                        $style
                    );
                    break;
                case 'TREE':

                    break;
            }
        }
        foreach ($facetCollection->getAggregations() as $aggregation) {
            $aggregationResultCollection->add($aggregation);
        }
    }

    /**
     * Transforms the default filter to an "property" filter
     *
     * @param Facet $facet
     * @return PropertyGroupEntity
     */
    protected function transformDefault(Facet $facet): PropertyGroupEntity
    {
        $options = new PropertyGroupOptionCollection();
        $elements = array_merge($facet->getElements(), $facet->getSelectedElements());
        foreach ($elements as $element) {
            $elementLabel = $element->getText();
            $option = new PropertyGroupOptionEntity();
            $option->setId(Uuid::randomHex());
            $option->setUniqueIdentifier(Uuid::randomHex());
            $option->setName($elementLabel);
            $option->setTranslated(['name' => $elementLabel]);
            $option->addExtension(DefaultFacetExtension::KEY, new DefaultFacetExtension(
                $facet->getName(), $element->getText(),
                $element->getTotalHits()
            ));
            $options->add($option);
        }

        $group = new PropertyGroupEntity();
        $group->setId(Uuid::randomHex());
        $group->setUniqueIdentifier(Uuid::randomHex());
        $group->setOptions($options);
        $group->setName($facet->getName());
        $group->setTranslated(['name' => $facet->getName()]);
        $group->setDisplayType('text');
        return $group;
    }

    /**
     * Transforms slider filters
     *
     * @param Facet $facet
     * @return StatsResult|null
     */
    protected function transformSlider(Facet $facet): ?StatsResult
    {
        $minValue = null;
        $maxValue = null;
        $elements = array_merge($facet->getElements(), $facet->getSelectedElements());
        foreach ($elements as $element) {
            $minValue = $element->getAbsoluteMinValue();
            $maxValue = $element->getAbsoluteMaxValue();
        }

        if (!$minValue || !$maxValue) {
            return null;
        }

        return new StatsResult(
            $facet->getName(),
            $minValue, $maxValue,
            null, null
        );
    }
}