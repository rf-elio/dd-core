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

namespace Elio\FactFinder\Core\FilterRestrictions;

use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Search\Request\NavigationRequest;
use Elio\FactFinder\Configuration\FactFinderConfigService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Throwable;

/**
 * Class FilterService
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterService
{

    public const LEVEL_GLOBAL = 1;
    public const LEVEL_SEARCH = 2;
    public const LEVEL_NAVIGATION = 3;
    public const LEVEL_CATEGORY = 10;
    private const MAX_DEEP_CATEGORY = 20;

    private EntityRepositoryInterface $filterRepository;
    private EntityRepositoryInterface $filterRestrictionsRepository;
    private EntityRepositoryInterface $categoryRepository;
    private FactFinderConfigService $configService;

    /**
     * FilterService constructor.
     * @param EntityRepositoryInterface $filterRepository
     * @param EntityRepositoryInterface $filterRestrictionsRepository
     * @param EntityRepositoryInterface $categoryRepository
     * @param FactFinderConfigService $configService
     */
    public function __construct(
        EntityRepositoryInterface $filterRepository,
        EntityRepositoryInterface $filterRestrictionsRepository,
        EntityRepositoryInterface $categoryRepository,
        FactFinderConfigService $configService
    ) {
        $this->filterRepository = $filterRepository;
        $this->filterRestrictionsRepository = $filterRestrictionsRepository;
        $this->categoryRepository = $categoryRepository;
        $this->configService = $configService;
    }

    /**
     * Get all allowed/blocked filters for such salesChannelId and level (if it is category level => categoryId have to be provided)
     *
     * Returns array [
     *              [ array of allowed filters with keys filterId and values filterName],
     *              [ array of blocked filters with keys filterId and values filterName]
     * ]
     *
     * if array of allowed/blocked filters is null - it means allow/block everything
     *
     * @param string|null $salesChannelId
     * @param int $level
     * @param ApiRequest $request
     * @return array
     */
    public function getFilters(?string $salesChannelId, int $level, ApiRequest $request): array
    {
        $context = Context::createDefaultContext();
        $categoryId = $request instanceof NavigationRequest ? $request->getCategoryId() : null;
        $filters = [null, null];

        $config = $this->configService->get($salesChannelId);
        $configParentCategories = $config->isRestrictionsParentCategories();
        $configIsOverridingTopToDown = $config->isRestrictionsOverridingTopToDown();

        // Global Level
        $restrictions = $this->getRestrictions($salesChannelId, $context, 'global');
        $filters = $this->applyRestrictionsToFiltersArray($filters, $restrictions, true);

        // Applying overriding restrictions
        if ($level == self::LEVEL_SEARCH) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'search');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$configIsOverridingTopToDown
            );
        } elseif ($level == self::LEVEL_NAVIGATION) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'navigation');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$configIsOverridingTopToDown
            );
        } elseif ($level == self::LEVEL_CATEGORY && $categoryId) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'navigation');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$configIsOverridingTopToDown
            );

            $categoriesTreeIds = [];
            if ($configParentCategories) {
                $maxDeepLevel = 0;
                /** @var CategoryEntity $category */
                $category = $this->categoryRepository->search(new Criteria([$categoryId]), $context)->first();
                if ($category) {
                    while ($category->getParentId() && $maxDeepLevel < self::MAX_DEEP_CATEGORY) {
                        $categoriesTreeIds[] = $category->getId();
                        $category = $this->categoryRepository->search(
                            new Criteria([$category->getParentId()]),
                            $context
                        )->first();
                        $maxDeepLevel++;
                    }
                    $categoriesTreeIds[] = $category->getId(); // most top category
                }
                $categoriesTreeIds = array_reverse($categoriesTreeIds);
            } else {
                $categoriesTreeIds[] = $categoryId;
            }

            foreach ($categoriesTreeIds as $currentCategoryId) {
                $restrictions = $this->getRestrictions($salesChannelId, $context, '', $currentCategoryId);
                $filters = $this->applyRestrictionsToFiltersArray(
                    $filters,
                    $restrictions,
                    !$configIsOverridingTopToDown
                );
            }
        }

        return $filters;
    }

    /**
     * Modifying inputted filters array
     * with provided FilterRestrictionsCollection and $isOverrides flag
     *
     * $filters = [ [...AllowedFiltersArray...], [...BlockedFiltersArray...] ]
     *
     * @param array $filters
     * @param FilterRestrictionsCollection $restrictions
     * @param bool $isOverrides
     * @return array
     */
    private function applyRestrictionsToFiltersArray(
        array $filters,
        FilterRestrictionsCollection $restrictions,
        bool $isOverrides
    ): array {
        $allowedFilters = null;
        $blockedFilters = null;

        foreach ($restrictions as $restriction) {
            if ($restriction->isAllowed()) {
                // allowed column
                $allowedFilters = $this->getFilterArrayAfterRestriction($filters, $restriction, $isOverrides);
            } else {
                // blocked column
                $blockedFilters = $this->getFilterArrayAfterRestriction($filters, $restriction, $isOverrides);
            }
        }

        return [$allowedFilters, $blockedFilters];
    }

    /**
     * @param array $filters
     * @param FilterRestrictionsEntity $restriction
     * @param bool $isOverrides
     * @return array
     */
    private function getFilterArrayAfterRestriction(
        array $filters,
        FilterRestrictionsEntity $restriction,
        bool $isOverrides
    ): ?array {
        if ($restriction->isAllChecked()) { // if Allow/Block All checked
            if ($isOverrides) { // if this restriction overrides top-level restrictions
                // we return everything allowed/blocked
                $result = null;
            } else { // if this restriction doesn't override top-level restrictions
                $result = $this->getMergedFilterArrayAfterRestriction($filters, $restriction, $this->getAllFilters());
            }
        } else { // if allow/block only selected checked (default)
            $restrictionFilters = $this->transformToSimpleForm($restriction->getFilters());
            if ($isOverrides) { // if this restriction overrides top-level restrictions
                // we return allowed/blocked only selected on this level
                $result = $restrictionFilters;
            } else { // if this restriction doesn't override top-level restrictions
                $result = $this->getMergedFilterArrayAfterRestriction($filters, $restriction, $restrictionFilters);
            }
        }
        return $result;
    }

    /**
     * @param array $filters
     * @param FilterRestrictionsEntity $restriction
     * @param array $filtersToApply
     * @return array|null
     */
    private function getMergedFilterArrayAfterRestriction(
        array $filters,
        FilterRestrictionsEntity $restriction,
        array $filtersToApply
    ): ?array {
        if ($filters[$restriction->isAllowed() ? 0 : 1] === null) { // if we already have null what's mean that we allow/block everything
            $result = null; // we keep it as it is
        } else {
            $result = [];
            if ($filters[$restriction->isAllowed() ? 1 : 0] !== null) { // if $filters[1:0] == null then we are blocking/allowing all already and can't override it
                // we add filters to allowed/blocked Filters which aren't blocked/allowed on level before
                // (bcs we aren't overriding rules)
                foreach ($filtersToApply as $filter) {
                    if (!in_array($filter, $filters[$restriction->isAllowed() ? 1 : 0], true)) {
                        $result[] = $filter;
                    }
                }
                // we merge them together
                $result = array_merge($result, $filters[$restriction->isAllowed() ? 0 : 1]);
            }
        }
        return $result;
    }

    /**
     * Returns array with keys of filterId and values filterPropertyName
     * for all filters in database;
     * @return array
     */
    private function getAllFilters(): array
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        /** @var FilterCollection $filters */
        $filters = $this->filterRepository->search($criteria, $context)->getEntities();
        return $this->transformToSimpleForm($filters);
    }

    /**
     * Returns array with keys of filterId and values filterPropertyName
     * for all filters in provided FilterCollection;
     * @param FilterCollection|null $filterCollection
     * @return array
     */
    private function transformToSimpleForm(?FilterCollection $filterCollection): array
    {
        $returning = [];
        /** @var FilterEntity $filter */
        foreach ($filterCollection as $filter) {
            $returning[$filter->getId()] = $filter->getPropertyName();
        }
        return $returning;
    }

    /**
     * @param string|null $salesChannelId
     * @param Context $context
     * @param string $layer
     * @param string|null $categoryId
     * @return EntityCollection
     */
    private function getRestrictions(
        ?string $salesChannelId,
        Context $context,
        string $layer,
        string $categoryId = null
    ): EntityCollection {
        $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, $layer, $categoryId);
        $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
        if (count($restrictions) == 0) {
            // if config for specified salesChannelId wasn't set up, then we get settings for all salesChannels
            $criteria = $this->getFilterRestrictionsCriteria(null, '', $categoryId);
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
        } else {
            if ($restrictions->first()->isInherited()) {
                // if config for specified salesChannelId inherited from settings for all salesChannels then we push it
                $criteria = $this->getFilterRestrictionsCriteria(null, '', $categoryId);
                $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            }
        }
        return $restrictions;
    }

    /**
     * Returning criteria to search filter restrictions columns
     * @param string|null $salesChannelId
     * @param string $layer
     * @param string|null $categoryId
     * @return Criteria
     */
    private function getFilterRestrictionsCriteria(
        ?string $salesChannelId,
        string $layer,
        string $categoryId = null
    ): Criteria {
        $criteria = new Criteria();
        $criteria->addAssociation('filters');
        $criteria->addFilter(
            new EqualsFilter('salesChannelId', $salesChannelId)
        );
        if ($categoryId) {
            $criteria->addFilter(
                new EqualsFilter('isCategory', true)
            );
            $criteria->addFilter(
                new EqualsFilter('categoryId', $categoryId)
            );
        } else {
            $criteria->addFilter(
                new EqualsFilter('layer', $layer)
            );
        }
        return $criteria;
    }
}