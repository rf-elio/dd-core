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
    }

    public function suggest(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();
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

        $ffSuggestions = $this->ffService->getSuggestions($request->query->get('search'));
        $result = $this->search($criteria, $context, $ffSuggestions);

        $result = ProductListingResult::createFrom($result);

        $this->eventDispatcher->dispatch(
            new ProductSuggestResultEvent($request, $result, $context),
            ProductEvents::PRODUCT_SUGGEST_RESULT
        );

        return $result;
    }

    private function search(
        Criteria $productSearchCriteria,
        SalesChannelContext $context,
        $ffSuggestions = []
    ):FactFinderSearchResult
    {
        $ids = [];
        $ffSuggestEntities = [];
        $manufacturerNameFilter = [];
        $categoryNameFilter = [];

        $categorySearchCriteria = new Criteria();
        $manufacturerSearchCriteria = new Criteria();

        $ffHasProductNameType = false;
        $ffHasCategoryType = false;
        $ffHasBrandType = false;

        foreach ($ffSuggestions as $ffSuggestion){

            if($ffSuggestion['type'] === FactFinderConfigurationInterface::ITEM_PRODUCT_TYPE){
                $ffHasProductNameType = true;
                $ids[] = $ffSuggestion['attributes']['id'];
            }

            if ($ffSuggestion['type'] === FactFinderConfigurationInterface::ITEM_CATEGORY_TYPE){
                $ffHasCategoryType = true;
                $categoryNameFilter[] = new EqualsFilter('category.name', htmlspecialchars_decode($ffSuggestion['name']));
            }

            if ($ffSuggestion['type'] === FactFinderConfigurationInterface::ITEM_SUPPLIER_TYPE){
                $ffHasBrandType = true;
                $manufacturerNameFilter[] = new EqualsFilter('name', htmlspecialchars_decode($ffSuggestion['name']));
            }
        }

        $productSearchCriteria->setIds($ids);
        $productSuggestResult = $this->productRepository->search($productSearchCriteria, $context);

        if($ffHasProductNameType){
            $ffSuggestEntities[] = $productSuggestResult->getEntities();
        }
        if ($ffHasCategoryType){
            $categorySearchCriteria->addFilter(new MultiFilter(
                MultiFilter::CONNECTION_OR,
                $categoryNameFilter
            ));
            $ffSuggestEntities[] = $this->categoryRepository->search($categorySearchCriteria, $context)->getEntities();
        }
        if ($ffHasBrandType){
            $manufacturerSearchCriteria->addFilter(new MultiFilter(
                MultiFilter::CONNECTION_OR,
                $manufacturerNameFilter
            ));
            $ffSuggestEntities[] = $this->productManufacturerRepository->search(
                $manufacturerSearchCriteria,
                $context->getContext()
            )->getEntities();
        }

        $result = new FactFinderSearchResult(
            count($ffSuggestions),
            $productSuggestResult->getEntities(),
            $productSuggestResult->getAggregations(),
            $productSearchCriteria,
            $context->getContext()
        );

        $result->setFfRawSearchResult($ffSuggestions);
        $result->setFfEntities($ffSuggestEntities);

        return $result;
    }

}
