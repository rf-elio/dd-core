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

use _HumbugBox3ab8cff0fda0\___PHPSTORM_HELPERS\this;
use Doctrine\DBAL\Connection;
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
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchTermInterpreterInterface;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\Bucket;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Query\ScoreQuery;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\StatsAggregation;

/**
 * Decorates the service ProductSearchGateway
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

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ProductSearchTermInterpreterInterface
     */
    private $interpreter;

    public function __construct(
        ProductSearchBuilderInterface $searchBuilder,
        EventDispatcherInterface $eventDispatcher,
        ElioFactFinderService $ffService,
        SalesChannelRepositoryInterface $repository,
        ProductSearchGatewayInterface $decorated,
        EntityRepositoryInterface $productManufacturerRepository,
        SalesChannelRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository,
        FactFinderHelper $ffHelper,
        Connection $connection,
        ProductSearchTermInterpreterInterface $interpreter
    )
    {
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
        $this->connection = $connection;
        $this->interpreter = $interpreter;

        $this->ffService->upsertRequestParam('productsPerPage', 500);
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
        //$result = $this->repository->search($criteria, $context);

        $result = ProductListingResult::createFrom($result);

        $this->eventDispatcher->dispatch(
            new ProductSearchResultEvent($request, $result, $context),
            ProductEvents::PRODUCT_SEARCH_RESULT
        );

        $result->addCurrentFilter('search', $request->query->get('search'));

        return $result;
    }

    private function ffSearch(
        Request $request,
        Criteria $criteria,
        SalesChannelContext $context
    ): FactFinderSearchResult
    {
        $criteria->resetQueries();
        $criteria->resetFilters();

        $postFilters = $criteria->getPostFilters();
        $i=0;
        foreach ($postFilters as $postFilter){
            foreach ($postFilter->getFields() as $field){
                if ($field !== 'product.listingPrices')
                    unset($postFilters[$i]);
            }
            ++$i;
        }

        $criteria->resetPostFilters();

        if(count($postFilters) > 0)
            $criteria->addPostFilter($postFilters[0]);

        $criteria->addFilter(
            new ProductAvailableFilter(
                $context->getSalesChannel()->getId(),
                ProductVisibilityDefinition::VISIBILITY_SEARCH
            )
        );

        $pattern = $this->interpreter->interpret($request->query->get('search'), $context->getContext());

        // Necessary for the functionality of the default sorting (Top results)
        $criteria->addQuery(
            new ScoreQuery(
                new ContainsFilter('product.searchKeywords.keyword', ''),
                $pattern->getOriginal()->getScore(),
                'product.searchKeywords.ranking'
            )
        );

        $this->handleFilters($request);

        $ffSearchResult = $this->ffService->search($request->query->get('search'));

        $productSearchResult = $this->ffHelper->getProducts(
            $context,
            $criteria,
            $this->getProductIds($ffSearchResult['records'])
        );

        $filters = $this->getFilters($ffSearchResult['groups'], $context);
        //$aggregations = $this->overrideAggregations($productSearchResult->getAggregations(), $filters, $context);

        $result = new FactFinderSearchResult(
            //$ffSearchResult['resultCount'],
            $productSearchResult->getTotal(),
            $productSearchResult->getEntities(),
            $productSearchResult->getAggregations(),
            //$aggregations,
            $criteria,
            $context->getContext()
        );
        //dd($ffSearchResult['groups']);

        $result->setFfRawData($ffSearchResult);
        $result->setFfFilters($filters);

        return $result;
    }

    private function getFilters(array $groups, SalesChannelContext $context): array
    {
        $filters = [];
        $PropertyGroups = [];
        $criteria = null;

        foreach ($groups as $group) {

            switch ($group['name']) {

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


                    $PropertyGroups[] = $this->getEntities(
                        $context->getContext(),
                        $this->propertyGroupOptionRepository,
                        $group['elements'],
                        'name'
                    );

                    /*
                    // Get grouped properties
                    $filters[strtolower($group['name'])] = $this->getEntities(
                        $context->getContext(),
                        $this->propertyGroupOptionRepository,
                        $group['elements'],
                        'name'
                    );
                    */

                    break;
            }
        }


        $filters['properties'] = $this->getListProperties($PropertyGroups);

        return $filters;
    }

    private function getEntities(
        $context,
        $repository,
        array $elements,
        string $filterField,
        string $elementField = ""
    ): array
    {
        $entities = [];

        if (empty($elementField))
            $elementField = $filterField;

        foreach ($elements as $element) {

            $criteria = new Criteria();
            $criteria->addFilter(
                new EqualsFilter($filterField, htmlspecialchars_decode(str_replace('&#039;', "'", $element[$elementField])))
            );


            $entities[] = [
                'entity' => $repository->search($criteria, $context)->first(),
                'recordCount' => $element['recordCount'],
                'searchParams' => $element['searchParams']
            ];
        }

        return $entities;
    }

    private function getListProperties(array $propertyGroups): array
    {
        $listProperties = [];

        foreach ($propertyGroups as $groups) {
            foreach ($groups as $property) {
                $listProperties[] = $property;
            }
        }

        return $listProperties;
    }

    private function overrideAggregations(
        AggregationResultCollection $aggregations,
        array $filters,
        SalesChannelContext $context
    ): AggregationResultCollection
    {
        /** @var Bucket[] $buckets */
        $buckets = [];
        $propertyGroupOptionIds = [];

        $manufacturerResult = new EntityResult('manufacturer', new ProductManufacturerCollection());

        foreach ($filters['manufacturer'] as $manufacturer) {
            $entity = $manufacturer['entity'];
            $manufacturerResult->add($entity);
        }

        foreach ($filters['properties'] as $property) {

            /** @var PropertyGroupOptionEntity $entity */
            $entity = $property['entity'];

            $buckets[] = new Bucket($entity->getId(), $property['recordCount'], null);

            $propertyGroupOptionIds[] = $entity->getId();
        }

        $propertyResult = new TermsResult('properties', $buckets);

        $criteria = new  Criteria();
        $criteria->setIds($propertyGroupOptionIds);
        $criteria->addAggregation(new TermsAggregation('properties', 'id'));

        $aggregations->set('manufacturer', $manufacturerResult);
        $aggregations->set('properties', $propertyResult);
        /*
        $aggregations->set(
            'properties',
            $this->propertyGroupOptionRepository->aggregate($criteria, $context->getContext())->get('properties')
        );
        */

        return $aggregations;
    }

    private function getProductIds(array $records):array
    {
        $ids = [];

        foreach ($records as $record) {
            $ids[] = $record['id'];
        }

        return array_filter($ids);
    }

    private function getManufacturerIds(Request $request): array
    {
        $ids = $request->query->get('manufacturer', '');
        $ids = explode('|', $ids);

        return array_filter($ids);
    }

    private function getPropertyIds(Request $request): array
    {
        $ids = $request->query->get('properties', '');
        $ids = explode('|', $ids);

        return array_filter($ids);
    }

    private function handleFilters(Request $request):void
    {
        $this->ffService->setManufacturerFilter($this->getManufacturerIds($request));
        $this->ffService->setPropertyFilter($this->getPropertyIds($request));
        $this->ffService->setRatingFilter($request->get('rating'));
        $this->ffService->setShippingFreeFilter($request->get('shipping-free'));
        //$this->ffService->setPriceFilter($request->get('min-price'), $request->get('max-price'));
    }

}
