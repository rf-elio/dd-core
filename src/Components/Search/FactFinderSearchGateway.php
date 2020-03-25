<?php declare(strict_types=1);

/**
 * Copyright (c) 2020, elio GmbH.
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

namespace Elio\FactFinder\Components\Search;

use Elio\FactFinder\Components\ElioFactFinderService;
use Elio\FactFinder\Components\Helper\FactFinderHelper;
use Elio\FactFinder\Service\FactFinderConfigurationInterface;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchGatewayInterface;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * Class FactFinderSearchGateway
 *
 * @category  Service Component
 * @package   Shopware\Plugins\FactFinder\Components\Search
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinderSearchGateway implements ProductSearchGatewayInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProductSearchBuilderInterface
     */
    private $searchBuilder;

    /**
     * @var ElioFactFinderService
     */
    private $ffService;

    /**
     * @var FactFinderConfigurationInterface
     */
    private $ffConfig;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $repository;

    /**
     * @var ProductSearchGatewayInterface
     */
    private $decorated;

    /**
     * @var EntityRepositoryInterface
     */
    private $productManufacturerRepository;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $propertyGroupOptionRepository;

    /**
     * @var FactFinderHelper
     */
    private $ffHelper;

    public function __construct(
        ProductSearchBuilderInterface $searchBuilder,
        EventDispatcherInterface $eventDispatcher,
        ElioFactFinderService $ffService,
        SalesChannelRepositoryInterface $repository,
        ProductSearchGatewayInterface $decorated,
        EntityRepositoryInterface $productManufacturerRepository,
        SalesChannelRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository,
        FactFinderHelper $ffHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->searchBuilder = $searchBuilder;
        $this->ffService = $ffService;
        $this->ffConfig = $ffService->getConfig();
        $this->repository = $repository;
        $this->decorated = $decorated;
        $this->productManufacturerRepository = $productManufacturerRepository;
        $this->categoryRepository = $categoryRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->ffHelper = $ffHelper;
    }

    public function search(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();

        $this->searchBuilder->build($request, $criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductSearchCriteriaEvent($request, $criteria, $context),
            ProductEvents::PRODUCT_SEARCH_CRITERIA
        );

        $result = $this->ffSearch($request, $criteria, $context);

        $result = ProductListingResult::createFrom($result);

        $this->eventDispatcher->dispatch(
            new ProductSearchResultEvent($request, $result, $context),
            ProductEvents::PRODUCT_SEARCH_RESULT
        );

        $result->addCurrentFilter('search', $request->query->get('search'));

        return $result;
    }

    private function ffSearch (
        Request $request,
        Criteria $criteria,
        SalesChannelContext $context
    ):FactFinderSearchResult
    {
        $criteria->resetSorting();
        $criteria->resetQueries();
        $criteria->resetFilters();
        //$criteria->resetAggregations();

        $criteria->addFilter(
            new ProductAvailableFilter(
                $context->getSalesChannel()->getId(),
                ProductVisibilityDefinition::VISIBILITY_SEARCH
            )
        );

        $this->ffService->upsertRequestParam('productsPerPage', 500);
        $ffSearchResult = $this->ffService->search($request->query->get('search'));
        $productSearchResult = $this->ffHelper->convertRecords($context, $criteria, $ffSearchResult['records']);

        $filters = $this->getFilters($ffSearchResult['groups'], $context);

        $aggregations = $this->overrideAggregations($productSearchResult->getAggregations(), $filters);

        $result = new FactFinderSearchResult(
            $ffSearchResult['resultCount'],
            $productSearchResult->getEntities(),
            $aggregations,
            $criteria,
            $context->getContext()
        );

        $result->setFfRawData($ffSearchResult);
        $result->setFfFilters($filters);

        return $result;
    }

    private function getFilters(array $groups, SalesChannelContext $context):array
    {
        $filters = [];
        $criteria = null;

        foreach ($groups as $group){

            switch ($group['name']){

                case FactFinderConfigurationInterface::FILTER_CATEGORY:

                    $filters['category'] = $this->getEntities(
                        $context,
                        $this->categoryRepository,
                        $group['elements'],
                        'category.name',
                        'name'
                    );

                    break;

                case FactFinderConfigurationInterface::FILTER_MANUFACTURER:

                    $filters['manufacturer'] = $this->getEntities(
                        $context->getContext(),
                        $this->productManufacturerRepository,
                        $group['elements'],
                        'name'
                    );

                    break;

                case FactFinderConfigurationInterface::FILTER_COLOR:
                case FactFinderConfigurationInterface::FILTER_CONTENT:
                case FactFinderConfigurationInterface::FILTER_LENGTH:
                case FactFinderConfigurationInterface::FILTER_SIZE:
                case FactFinderConfigurationInterface::FILTER_TEXTILE:
                case FactFinderConfigurationInterface::FILTER_WIDTH:

                    $filters[strtolower($group['name'])] = $this->getEntities(
                        $context->getContext(),
                        $this->propertyGroupOptionRepository,
                        $group['elements'],
                        'name'
                    );

                    break;
            }
        }

        return $filters;
    }

    private function getEntities(
        $context,
        $repository,
        array $elements,
        string $filterField,
        string $elementField = ''
    ):array
    {
        $entities = [];

        if (empty($elementField))
            $elementField = $filterField;

        foreach ($elements as $element){

            $criteria = new Criteria();
            $criteria->addFilter(
                new EqualsFilter($filterField, htmlspecialchars_decode($element[$elementField]))
            );

            $entities[] = [
                'entity' => $repository->search($criteria, $context)->first(),
                'recordCount' => $element['recordCount'],
                'searchParams' => $element['searchParams']
            ];
        }

        return $entities;
    }

    private function overrideAggregations(
        AggregationResultCollection $aggregations,
        array $filters
    ):AggregationResultCollection
    {
        $manufacturerResult = new EntityResult('manufacturer', new ProductManufacturerCollection());
        //$propertyResult = new TermsResult('properties');

        foreach ($filters['manufacturer'] as $manufacturer){
            $manufacturerResult->add($manufacturer['entity']);
        }

        $aggregations->set('manufacturer', $manufacturerResult);

        return $aggregations;
    }
}
