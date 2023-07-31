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
use Elio\FactFinder\Api\Search\Request\NavigationRequestProduct;
use Elio\FactFinder\Configuration\FactFinderConfigService;
use Elio\FactFinder\Configuration\LanguageHelper;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class FilterService
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterService implements FilterInterface
{
    public const LEVEL_GLOBAL = 1;
    public const LEVEL_SEARCH = 2;
    public const LEVEL_NAVIGATION = 3;
    public const LEVEL_CATEGORY = 10;
    private const MAX_DEEP_CATEGORY = 20;

    private EntityRepository $filterRestrictionsRepository;
    private EntityRepository $categoryRepository;
    private FactFinderConfigService $configService;

    /**
     * FilterService constructor.
     * @param EntityRepository $filterRestrictionsRepository
     * @param EntityRepository $categoryRepository
     * @param FactFinderConfigService $configService
     */
    public function __construct(
        EntityRepository $filterRestrictionsRepository,
        EntityRepository $categoryRepository,
        FactFinderConfigService $configService
    ) {
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
     * @param SalesChannelContext $salesChannelContext
     * @param int $level
     * @param ApiRequest $request
     * @return array
     */
    public function getFilters(SalesChannelContext $salesChannelContext, int $level, ApiRequest $request): array
    {
        $context = $salesChannelContext->getContext();
        $languageId = LanguageHelper::getLanguageIdBySalesChannelContext($salesChannelContext);
        $categoryId = $request instanceof NavigationRequestProduct ? $request->getCategoryId() : null;
        $allowedFilters = null; //everything allowed by default
        $blockedFilters = []; // nothing blocked by default

        $config = $this->configService->getByContext($salesChannelContext);
        $configParentCategories = $config->isRestrictionsParentCategories();

        // Global Level
        $salesChannelId = $salesChannelContext->getSalesChannelId();
        $restrictions = $this->getRestrictions($salesChannelId, $languageId, $context, 'global');
        $allowedFilters = $this->applyAllowedRestrictions($allowedFilters, $restrictions, true);
        $blockedFilters = $this->applyBlockedRestrictions($blockedFilters, $restrictions, true);

        // Applying overriding restrictions
        if ($level === self::LEVEL_SEARCH) {
            $restrictions = $this->getRestrictions($salesChannelId, $languageId, $context, 'search');
            $allowedFilters = $this->applyAllowedRestrictions($allowedFilters, $restrictions);
            $blockedFilters = $this->applyBlockedRestrictions($blockedFilters, $restrictions);
        } elseif ($level === self::LEVEL_NAVIGATION) {
            $restrictions = $this->getRestrictions($salesChannelId, $languageId, $context, 'navigation');
            $allowedFilters = $this->applyAllowedRestrictions($allowedFilters, $restrictions);
            $blockedFilters = $this->applyBlockedRestrictions($blockedFilters, $restrictions);
        } elseif ($level === self::LEVEL_CATEGORY && $categoryId) {
            $restrictions = $this->getRestrictions($salesChannelId, $languageId, $context, 'navigation');
            $allowedFilters = $this->applyAllowedRestrictions($allowedFilters, $restrictions);
            $blockedFilters = $this->applyBlockedRestrictions($blockedFilters, $restrictions);

            $categoriesTreeIds = [];
            if ($configParentCategories) {
                $maxDeepLevel = 0;
                /** @var CategoryEntity|null $category */
                $category = $this->categoryRepository->search(new Criteria([$categoryId]), $context)->first();
                if ($category) {
                    while ($category->getParentId() && $maxDeepLevel < self::MAX_DEEP_CATEGORY) {
                        $categoriesTreeIds[] = $category->getId();
                        /** @var CategoryEntity|null $category */
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
                $restrictions = $this->getRestrictions($salesChannelId, $languageId, $context, '', $currentCategoryId);
                if (count($restrictions->getElements()) != 0) {
                    $allowedFilters = $this->applyAllowedRestrictions($allowedFilters, $restrictions);
                    $blockedFilters = $this->applyBlockedRestrictions($blockedFilters, $restrictions);
                }
            }
        }

        return [$allowedFilters, $blockedFilters];
    }

    /**
     * @param array|null $parentFilters
     * @param EntityCollection $restrictions
     * @param bool $overrideParent
     * @return array|null
     */
    protected function applyAllowedRestrictions(
        ?array $parentFilters,
        EntityCollection $restrictions,
        bool $overrideParent = false
    ): ?array
    {
        $result = $parentFilters;
        /** @var FilterRestrictionsEntity $restriction */
        foreach ($restrictions as $restriction) {
            if ($restriction->isAllowed()) {
                if ($restriction->isAllChecked()) { // if Allow/Block All checked
                    if ($overrideParent) {
                        $result = null;
                    } else {
                        //only apply filters allowed in parent and current level
                        $result = $parentFilters;
                    }
                } else { // if allow/block only selected checked (default)
                    $restrictionFilters = $this->transformToSimpleForm($restriction->getFilters());
                    if ($overrideParent) {
                        $result = array_values($restrictionFilters);
                    } else {
                        //only apply filters allowed in parent and current level
                        $result = $parentFilters === null ? $restrictionFilters : array_intersect($parentFilters, $restrictionFilters);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param array|null $parentFilters
     * @param EntityCollection $restrictions
     * @param bool $overrideParent
     * @return array|null
     */
    protected function applyBlockedRestrictions(
        ?array $parentFilters,
        EntityCollection $restrictions,
        bool $overrideParent = false
    ): ?array
    {
        $result = $parentFilters;
        /** @var FilterRestrictionsEntity $restriction */
        foreach ($restrictions as $restriction) {
            if (!$restriction->isAllowed()) {
                if ($restriction->isAllChecked()) { // if Allow/Block All checked
                    //parent filters don't matter if everything is blocked on current level
                    $result = null;
                } else { // if allow/block only selected checked (default)
                    $restrictionFilters = $this->transformToSimpleForm($restriction->getFilters());
                    if ($overrideParent) {
                        $result = array_values($restrictionFilters);
                    } else {
                        //combine filters blocked in parent and current level
                        $result = array_unique(array_merge($restrictionFilters, $parentFilters));
                    }
                }
            }
        }
        return $result;
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
     * @param string $salesChannelId
     * @param string $languageId
     * @param Context $context
     * @param string $layer
     * @param string|null $categoryId
     * @return EntityCollection
     */
    private function getRestrictions(
        string $salesChannelId,
        string $languageId,
        Context $context,
        string $layer,
        string $categoryId = null
    ): EntityCollection {
        $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, $languageId, $layer, $categoryId);
        $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
        if (count($restrictions->getElements()) == 0) {
            // if config for specified salesChannelId AND languageId wasn't set up, then we get settings for all languages for this salesChannelId
            $criteria = $this->getFilterRestrictionsCriteria($salesChannelId, null, $layer, $categoryId);
            $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
            if (count($restrictions->getElements()) == 0) {
                // if config for specified salesChannelId AND languageId wasn't set up, then we get settings for all salesChannels for this languageId
                $criteria = $this->getFilterRestrictionsCriteria(null, $languageId, $layer, $categoryId);
                $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
                if (count($restrictions->getElements()) == 0) {
                    // if config for all salesChannels AND languageId wasn't set up, then we get settings for all salesChannels for all languages
                    $criteria = $this->getFilterRestrictionsCriteria(null, null, $layer, $categoryId);
                    $restrictions = $this->filterRestrictionsRepository->search($criteria, $context)->getEntities();
                }
            }
        }
        return $restrictions;
    }

    /**
     * Returning criteria to search filter restrictions columns
     * @param string|null $salesChannelId
     * @param string|null $languageId
     * @param string $layer
     * @param string|null $categoryId
     * @return Criteria
     */
    private function getFilterRestrictionsCriteria(
        ?string $salesChannelId,
        ?string $languageId,
        string $layer,
        string $categoryId = null
    ): Criteria {
        $criteria = new Criteria();
        $criteria->addAssociation('filters');
        $criteria->addFilter(
            new EqualsFilter('salesChannelId', $salesChannelId),
            new NotFilter(NotFilter::CONNECTION_AND, [new EqualsFilter('isInherited', true)])
        );
        $criteria->addFilter(
            new EqualsFilter('languageId', $languageId)
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
