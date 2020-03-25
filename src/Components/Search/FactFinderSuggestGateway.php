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

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\Suggest\ProductSuggestGatewayInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\ProductEvents;
use Elio\FactFinder\Service\FactFinderConfigurationInterface;
use Elio\FactFinder\Components\ElioFactFinderService;

/**
 *
 * Class FactFinderSuggestGateway
 *
 * @category  Service Component
 * @package   Shopware\Plugins\FactFinder\Components\Search
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinderSuggestGateway implements ProductSuggestGatewayInterface
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
    private $productRepository;

    /**
     * @var ProductSuggestGatewayInterface
     */
    private $decorated;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $productManufacturerRepository;

    /**
     * @var EntityCollection
     */
    private $entities;

    public function __construct(
        ProductSearchBuilderInterface $searchBuilder,
        EventDispatcherInterface $eventDispatcher,
        ElioFactFinderService $ffService,
        SalesChannelRepositoryInterface $productRepository,
        ProductSuggestGatewayInterface $decorated,
        SalesChannelRepositoryInterface $categoryRepository,
        EntityRepositoryInterface $productManufacturerRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->searchBuilder = $searchBuilder;
        $this->ffService = $ffService;
        $this->ffConfig = $ffService->getConfig();
        $this->productRepository = $productRepository;
        $this->decorated = $decorated;
        $this->categoryRepository = $categoryRepository;
        $this->productManufacturerRepository = $productManufacturerRepository;
        $this->entities = new EntityCollection();
    }

    public function suggest(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();

        $criteria->setLimit(10);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
        $criteria->addFilter(
            new ProductAvailableFilter(
                $context->getSalesChannel()->getId(),
                ProductVisibilityDefinition::VISIBILITY_SEARCH
            )
        );

        $this->searchBuilder->build($request, $criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductSuggestCriteriaEvent($request, $criteria, $context),
            ProductEvents::PRODUCT_SUGGEST_CRITERIA
        );

        $result = $this->ffSuggest($request, $criteria, $context);

        $result = ProductListingResult::createFrom($result);

        $this->eventDispatcher->dispatch(
            new ProductSuggestResultEvent($request, $result, $context),
            ProductEvents::PRODUCT_SUGGEST_RESULT
        );

        return $result;
    }

    private function ffSuggest(
        Request $request,
        Criteria $productCriteria,
        SalesChannelContext $context
    ):FactFinderSearchResult
    {
        $extraEntities = [];

        $ffSuggestions = $this->ffService->getSuggestions($request->query->get('search'));

        $entities = $this->toEntityCollection(
            FactFinderConfigurationInterface::ITEM_PRODUCT_TYPE,
            $ffSuggestions,
            $productCriteria,
            $context
        );

        $extraEntities['category'] = $this->toEntityCollection(
            FactFinderConfigurationInterface::ITEM_CATEGORY_TYPE,
            $ffSuggestions,
            new Criteria(),
            $context
        );

        $extraEntities['manufacturer'] = $this->toEntityCollection(
            FactFinderConfigurationInterface::ITEM_BRAND_TYPE,
            $ffSuggestions,
            new Criteria(),
            $context
        );

        $searchTerns = $this->getSearchTerms($ffSuggestions);

        $result = new FactFinderSearchResult(
            count($ffSuggestions),
            $entities,
            null,// suggestion request supports no aggregations or filters
            $productCriteria,
            $context->getContext()
        );

        $result->setFfRawData($ffSuggestions);
        $result->setFfEntities($extraEntities);
        $result->setFfSearchTerms($searchTerns);

        return $result;
    }

    private function toEntityCollection(
        string $type,
        array $suggestions,
        Criteria $criteria,
        SalesChannelContext $context
    ):EntityCollection
    {
        switch ($type){
            case FactFinderConfigurationInterface::ITEM_PRODUCT_TYPE:

                $ids = [];
                foreach ($suggestions as $suggestion){
                    if ($suggestion['type'] === $type){
                        $ids[] = $suggestion['attributes']['id'];
                    }
                }
                $criteria->setIds($ids);
                return $this->productRepository->search($criteria, $context)->getEntities();

            case FactFinderConfigurationInterface::ITEM_CATEGORY_TYPE:

                $criteria->resetFilters();

                $criteria = $this->addFilter(
                    $type,
                    $criteria,
                    $suggestions,
                    'category.name',
                    'name'
                );

                if(count($criteria->getFilters()) === 0)
                    return new CategoryCollection();

                return $this->categoryRepository->search(
                    $criteria,
                    $context)->getEntities();

            case FactFinderConfigurationInterface::ITEM_BRAND_TYPE:

                $criteria->resetFilters();

                $criteria = $this->addFilter(
                    $type,
                    $criteria,
                    $suggestions,
                    'name'
                );

                if(count($criteria->getFilters()) === 0)
                    return new ProductManufacturerCollection();

                return $this->productManufacturerRepository->search(
                    $criteria,
                    $context->getContext())->getEntities();

        }
    }

    private function addFilter(
        string $type,
        Criteria $criteria,
        array $suggestions,
        string $filterField,
        string $suggestionField = ''
    ): Criteria
    {
        $filters = [];

        if (empty($suggestionField))
            $suggestionField = $filterField;

        foreach ($suggestions as $suggestion){
            if ($suggestion['type'] === $type)
            $filters[] = new EqualsFilter($filterField, htmlspecialchars_decode($suggestion[$suggestionField]));
        }
        if (count($filters) > 0){
            $criteria->addFilter(new MultiFilter(
                MultiFilter::CONNECTION_OR,
                $filters
            ));
        }
        return $criteria;
    }

    private function addEntities(EntityCollection $collection):void
    {
        foreach ($collection->getElements() as $entity){
            $this->entities->add($entity);
        }
    }

    private function resetEntities():void
    {
        $this->entities = new EntityCollection();
    }

    private function getSearchTerms(array $suggestions):array
    {
        $searchTerms = [];

        foreach ($suggestions as $suggestion){
            if ($suggestion['type'] === FactFinderConfigurationInterface::ITEM_SEARCHTERM_TYPE){
                $searchTerms[] = $suggestion['name'];
            }
        }

        return $searchTerms;
    }
}
