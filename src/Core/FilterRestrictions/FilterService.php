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
    private const PLUGIN_CONFIG_PREFIX = 'FactFinder.config';

    public const LEVEL_GLOBAL = 1;
    public const LEVEL_SEARCH = 2;
    public const LEVEL_NAVIGATION = 3;
    public const LEVEL_CATEGORY = 10;
    private const MAX_DEEP_CATEGORY = 20;

    private EntityRepositoryInterface $propertyRepository;
    private EntityRepositoryInterface $filterRepository;
    private EntityRepositoryInterface $filterRestrictionsRepository;
    private EntityRepositoryInterface $categoryRepository;
    private LoggerInterface $logger;
    private SystemConfigService $systemConfigService;
    /**
     * @var bool|null
     */
    private $configParentCategories;
    /**
     * @var bool|null
     */
    private $configIsOverridingTopToDown;

    /**
     * FilterService constructor.
     * @param EntityRepositoryInterface $propertyRepository
     * @param EntityRepositoryInterface $filterRepository
     * @param EntityRepositoryInterface $filterRestrictionsRepository
     * @param EntityRepositoryInterface $categoryRepository
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $propertyRepository,
        EntityRepositoryInterface $filterRepository,
        EntityRepositoryInterface $filterRestrictionsRepository,
        EntityRepositoryInterface $categoryRepository,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->filterRepository = $filterRepository;
        $this->filterRestrictionsRepository = $filterRestrictionsRepository;
        $this->systemConfigService = $systemConfigService;
        $this->logger = $logger;

        $this->configParentCategories = $this->systemConfigService->get(
                self::PLUGIN_CONFIG_PREFIX . '.restrictionsParentCategories'
            ) ?? true;
        $this->configIsOverridingTopToDown = $this->systemConfigService->get(
                self::PLUGIN_CONFIG_PREFIX . '.restrictionsOverridingTopToDown'
            ) ?? true;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Sync filter from property for propertyId
     * @param Context $context
     * @param string $propertyId
     */
    public function syncOne(Context $context, string $propertyId)
    {
        /** @var PropertyGroupEntity $property */
        $property = $this->propertyRepository->search(new Criteria([$propertyId]), $context)->first();

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('propertyId', $property->getId())
        );
        $filter = $this->filterRepository->search($criteria, $context);
        if ($filter->getTotal() > 0) {
            // updating
            /** @var FilterEntity $filterEntity */
            $filterEntity = $filter->first();
            $this->filterRepository->update(
                ['id' => $filterEntity->getId(), 'propertyName' => $property->getName()],
                $context
            );
        } else {
            // creating
            $this->filterRepository->create(
                ['propertyName' => $property->getName(), 'propertyId' => $property->getId(), 'isCustom' => false],
                $context
            );
        }
    }

    /**
     * Sync all properties to filters, sync propertyNames, creating new filters, deleting old filters
     * @param Context $context
     */
    public function syncAll(Context $context)
    {
        /**
         * Getting all properties
         */
        $properties = $this->propertyRepository->search(new Criteria(), $context);
        $propertiesNames = [];
        $propertiesNamesUpdated = [];
        /** @var PropertyGroupEntity $property */
        foreach ($properties as $property) {
            $propertiesNames[$property->getId()] = $property->getName();
        }

        /**
         * Updating properties names
         */
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('propertyId', array_keys($propertiesNames))
        );
        $filters = $this->filterRepository->search($criteria, $context);

        $dataToUpdate = [];
        /** @var FilterEntity $filter */
        foreach ($filters as $filter) {
            $dataToUpdate[] = ['id' => $filter->getId(), 'propertyName' => $propertiesNames[$filter->getPropertyId()]];
            $propertiesNamesUpdated[$filter->getPropertyId()] = true;
        }
        try {
            if (count($dataToUpdate) > 0) {
                $this->filterRepository->update($dataToUpdate, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot update filters with this property ids',
                ['$dataToUpdate' => $dataToUpdate]
            );

            throw new RuntimeException(
                'Sync failed, look logs for more info'
            );
        }

        /**
         * Deleting old filters
         */
        $criteriaToDelete = new Criteria();
        $criteriaToDelete->addFilter(
            new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('propertyId', array_keys($propertiesNames))])

        );
        $filtersIdsToDelete = $this->filterRepository->searchIds($criteriaToDelete, $context)->getIds();
        $dataToDelete = [];
        foreach ($filtersIdsToDelete as $id) {
            $dataToDelete[] = ['id' => $id];
        }
        try {
            if (count($dataToDelete) > 0) {
                $this->filterRepository->delete($dataToDelete, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot delete filters with this property ids',
                ['$dataToDelete' => $dataToDelete]
            );

            throw new RuntimeException(
                'Sync failed, look logs for more info'
            );
        }

        /**
         * Creating filters for new properties
         */
        $propertyIdsToCreate = array_diff_key($propertiesNames, $propertiesNamesUpdated);
        $dataToCreate = [];
        foreach ($propertyIdsToCreate as $propertyId => $propertyName) {
            $dataToCreate[] = [
                'propertyName' => $propertyName,
                'propertyId' => $propertyId,
                'isCustom' => false
            ];
        }
        try {
            if (count($dataToCreate) > 0) {
                $this->filterRepository->create($dataToCreate, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot create filters with this property ids and names',
                ['$dataToCreate' => $dataToCreate]
            );

            throw new RuntimeException(
                'Sync failed, look logs for more info'
            );
        }
    }

    /**
     * Get all allowed filters for such salesChannelId and level (if it is category level => categoryId have to be provided)
     * @param string|null $salesChannelId
     * @param int $level
     * @param string|null $categoryId
     * @return array
     */
    public function getAllowedFilters(?string $salesChannelId, int $level, ApiRequest $request): array
    {
        $context = Context::createDefaultContext();
        $categoryId = $request instanceof NavigationRequest ? $request->getCategoryId() : null;
        $filters = [];

        // Global Level
        $restrictions = $this->getRestrictions($salesChannelId, $context, 'global');
        $filters = $this->applyRestrictionsToFiltersArray($filters, $restrictions, true);

        // Applying overriding restrictions
        if ($level == self::LEVEL_SEARCH) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'search');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$this->configIsOverridingTopToDown
            );
        } elseif ($level == self::LEVEL_NAVIGATION) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'navigation');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$this->configIsOverridingTopToDown
            );
        } elseif ($level == self::LEVEL_CATEGORY && $categoryId) {
            $restrictions = $this->getRestrictions($salesChannelId, $context, 'navigation');
            $filters = $this->applyRestrictionsToFiltersArray(
                $filters,
                $restrictions,
                !$this->configIsOverridingTopToDown
            );

            $categoriesTreeIds = [];
            if ($this->configParentCategories) {
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
                    !$this->configIsOverridingTopToDown
                );
            }
        }

        return $filters;
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
        if (count(
                $restrictions
            ) == 0) { // if config for specified salesChannelId wasn't set up, then we get settings for all salesChannels
            $criteria = $this->getFilterRestrictionsCriteria(null, '', $categoryId);
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
        }

        return $restrictions;
    }

    /**
     * Modify array with keys of filterId and values ['propertyName'=>filterPropertyName, 'isAllowed'=>true]
     * with provided FilterRestrictionsCollection and $isOverrides flag
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
        $allowedFilters = [];
        $blockedFilters = [];

        foreach ($restrictions as $restriction) {
            if ($restriction->isAllowed()) { // allowed column
                if ($restriction->isAllChecked()) { // if Allow All checked
                    if ($isOverrides) { // if this restriction overrides top-level restrictions
                        return $this->getAllFilters(true); // we return everything allowed
                    } else { // if this restriction doesn't override top-level restrictions
                        // we collect all filters to merge with current rules
                        $allowedFilters = $this->getAllFilters(true);
                    }
                } else { // if allow only selected checked (default)
                    $allowedFilters = $this->transformToSimpleForm($restriction->getFilters(), true);
                }
            } else { // blocked column
                if ($restriction->isAllChecked()) { // if Block All checked
                    if ($isOverrides) { // if this restriction overrides top-level restrictions
                        return $this->getAllFilters(false); // we block everything, no matter what was before
                    } else { // if this restriction doesn't override top-level restrictions
                        // we collect all filters to merge with current rules
                        $blockedFilters = $this->getAllFilters(false);
                    }
                } else { // if block only selected checked (default)
                    $blockedFilters = $this->transformToSimpleForm($restriction->getFilters(), false);
                }
            }
        }

        $currentLevelFiltersArray = $this->mergeFiltersArrays(
            $allowedFilters,
            $blockedFilters,
            false
        );
        return $this->mergeFiltersArrays($filters, $currentLevelFiltersArray, $isOverrides);
    }

    /**
     * Returns array with keys of filterId and
     * values ['propertyName'=>filterPropertyName, 'isAllowed'=>$_isAllowed]
     * for all filters in provided FilterCollection;
     * @param FilterCollection|null $filterCollection
     * @param bool $_isAllowed
     * @return array
     */
    private function transformToSimpleForm(?FilterCollection $filterCollection, bool $_isAllowed): array
    {
        $returning = [];
        /** @var FilterEntity $filter */
        foreach ($filterCollection as $filter) {
            $returning[$filter->getId()] = ['propertyName' => $filter->getPropertyName(), 'isAllowed' => $_isAllowed];
        }
        return $returning;
    }

    /**
     * Merging 2 arrays in 1 with overriding order
     * @param array|null $firstFilterArray
     * @param array|null $secondFilterArray
     * @param bool $isSecondOverride
     * @return array
     */
    private function mergeFiltersArrays(
        ?array $firstFilterArray,
        ?array $secondFilterArray,
        bool $isSecondOverride = false
    ): array {
        if ($isSecondOverride) {
            return array_merge($firstFilterArray, $secondFilterArray);
        } else {
            return array_merge($secondFilterArray, $firstFilterArray);;
        }
    }

    /**
     * Returns array with keys of filterId and
     * values ['propertyName'=>filterPropertyName, 'isAllowed'=>$_isAllowed]
     * for all filters in database;
     * @param bool $_isAllowed
     * @return array
     */
    private function getAllFilters(bool $_isAllowed): array
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        /** @var FilterCollection $filters */
        $filters = $this->filterRepository->search($criteria, $context)->getEntities();
        return $this->transformToSimpleForm($filters, $_isAllowed);;
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



    /**
     * below functions probably will be removed (not used anywhere)
     */

    /**
     * Get info about filter by filterId, SalesChannelId and FilterService::LEVEL_% with optional CategoryId
     * @param string $filterId
     * @param string $salesChannelId
     * @param int $level
     * @param string|null $categoryId
     * @return bool
     */
    public function getFilterInfo(string $filterId, string $salesChannelId, int $level, string $categoryId = null): bool
    {
        $context = Context::createDefaultContext();
        $isFilterAllowed = false;

        // Global Level
        $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, 'global');
        $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
        [$newFilterAllowed, $isModified] = $this->applyFilterRestrictions($filterId, $restrictions);
        if ($isModified) {
            $isFilterAllowed = $newFilterAllowed;
        }

        // Applying overriding restrictions
        if ($level == self::LEVEL_SEARCH) {
            $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, 'search');
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            [$newFilterAllowed, $isModified] = $this->applyFilterRestrictions($filterId, $restrictions);
            if ($isModified) {
                $isFilterAllowed = $newFilterAllowed;
            }
        } elseif ($level == self::LEVEL_NAVIGATION) {
            $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, 'navigation');
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            [$newFilterAllowed, $isModified] = $this->applyFilterRestrictions($filterId, $restrictions);
            if ($isModified) {
                $isFilterAllowed = $newFilterAllowed;
            }
        } elseif ($level == self::LEVEL_CATEGORY) {
            $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, 'navigation');
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            [$newFilterAllowed, $isModified] = $this->applyFilterRestrictions($filterId, $restrictions);
            if ($isModified) {
                $isFilterAllowed = $newFilterAllowed;
            }
            $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, '', $categoryId);
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            [$newFilterAllowed, $isModified] = $this->applyFilterRestrictions($filterId, $restrictions);
            if ($isModified) {
                $isFilterAllowed = $newFilterAllowed;
            }
        }

        return $isFilterAllowed;
    }

    /**
     * Returns [ifFilterAllowed, isModified]
     * by default ifFilterAllowed is false, and if this default was modified then isModified will be true
     * @param string $filterId
     * @param FilterRestrictionsCollection $restrictions
     * @return array
     */
    private function applyFilterRestrictions(string $filterId, FilterRestrictionsCollection $restrictions): array
    {
        [$isFilterAllowed, $isModified] = [false, false];
        foreach ($restrictions as $restriction) {
            if ($restriction->isAllowed()) {
                // filter in allowed column
                if ($restriction->isAllChecked()) {
                    $isFilterAllowed = true;
                    $isModified = true;
                } else {
                    if (array_key_exists($filterId, $restriction->getFilters()->getIds())) {
                        $isFilterAllowed = true;
                        $isModified = true;
                    }
                }
            } else {
                // filter in blocked column
                if ($restriction->isAllChecked()) {
                    $isFilterAllowed = false;
                    $isModified = true;
                } else {
                    if (array_key_exists($filterId, $restriction->getFilters()->getIds())) {
                        $isFilterAllowed = false;
                        $isModified = true;
                    }
                }
            }
        }
        return [$isFilterAllowed, $isModified];
    }
}