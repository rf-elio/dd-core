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
use Elio\FactFinder\Service\FactFinderConfigurationInterface;
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

    public function __construct(
        ProductSearchBuilderInterface $searchBuilder,
        EventDispatcherInterface $eventDispatcher,
        ElioFactFinderService $ffService,
        SalesChannelRepositoryInterface $repository,
        ProductSearchGatewayInterface $decorated,
        EntityRepositoryInterface $productManufacturerRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->searchBuilder = $searchBuilder;
        $this->ffService = $ffService;
        $this->ffConfig = $ffService->getConfig();
        $this->repository = $repository;
        $this->decorated = $decorated;
        $this->productManufacturerRepository = $productManufacturerRepository;
    }

    public function search(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new ProductAvailableFilter(
                $context->getSalesChannel()->getId(),
                ProductVisibilityDefinition::VISIBILITY_SEARCH
            )
        );

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
        $ids = [];

        $productCriteria = new Criteria(); //must create this new one to get product result
        $productCriteria->setLimit($criteria->getLimit());

        $this->ffService->upsertRequestParam('productsPerPage', $productCriteria->getLimit());
        $ffSearchResult = $this->ffService->search($request->query->get('search'));

        foreach ($ffSearchResult['records'] as $record){
            $ids[] = $record['id'];
        }

        $productCriteria->setIds($ids);
        $productSearchResult = $this->repository->search($productCriteria, $context);

        $result = new FactFinderSearchResult(
            $ffSearchResult['resultCount'],
            $productSearchResult->getEntities(),
            $productSearchResult->getAggregations(),
            $criteria,
            $context->getContext()
        );

        $filters = $this->getFilters($ffSearchResult['groups'], $context);

        $result->setFfRawData($ffSearchResult);
        $result->setFfAsn($filters);

        return $result;
    }

    private function getFilters(array $groups, SalesChannelContext $context):array
    {
        /** @var EntityCollection[] $entities */
        $filters = [];
        $criteria = null;

        foreach ($groups as $group){
            switch ($group['name']){
                case FactFinderConfigurationInterface::CATEGORY_FILTER:
                    break;

                case FactFinderConfigurationInterface::MANUFACTURER_FILTER:

                    $filters['manufacturer'] = $this->getEntities(
                        $context,
                        $group['elements'],
                        'name',
                        $this->productManufacturerRepository
                    );

                    break;
            }
        }
        return $filters;
    }

    private function getEntities(
        SalesChannelContext $context,
        array $elements,
        string $field,
        EntityRepositoryInterface $repository
    ):array
    {
        $entities = [];

        foreach ($elements as $element){
            $criteria = new Criteria();
            $criteria->addFilter(
                new EqualsFilter($field, htmlspecialchars_decode($element[$field]))
            );
            $entities[] = [
                'entity' => $repository->search($criteria, $context->getContext())->first(),
                'recordCount' => $element['recordCount'],
                'searchParams' => $element['searchParams']
            ];
        }

        return $entities;
    }
}
