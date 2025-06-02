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

namespace Elio\ElioDataDiscovery\Core\FilterRestrictions;

use Core\FilterRestrictions\Exception\FilterApplyException;
use Elio\ElioDataDiscovery\Api\Request\ApiRequest;
use Elio\ElioDataDiscovery\Api\Search\Request\NavigationRequestProduct;
use Elio\ElioDataDiscovery\Api\Search\Request\ProductSearchRequest;
use Elio\ElioDataDiscovery\Configuration\Configuration;
use Elio\ElioDataDiscovery\Configuration\ElioDataDiscoveryConfigServiceInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class FilterService
 * @package Elio\ElioDataDiscovery\Core\FilterRestrictions
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

    public const RESTRICTION_GLOBAL = 'global';
    public const RESTRICTION_SEARCH = 'search';
    public const RESTRICTION_NAVIGATION = 'navigation';

    /**
     * FilterService constructor.
     * @param EntityRepository $filterRestrictionsRepository
     * @param EntityRepository $filterRepository
     * @param EntityRepository $categoryRepository
     * @param ElioDataDiscoveryConfigServiceInterface $configService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly EntityRepository $filterRestrictionsRepository,
        private readonly EntityRepository $filterRepository,
        private readonly EntityRepository $categoryRepository,
        private readonly ElioDataDiscoveryConfigServiceInterface $configService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Gets filters by provided type
     *
     * @param string $type
     * @param SalesChannelContext $context
     * @return EntitySearchResult
     */
    public function getFilterByType(string $type, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('type', $type));
        return $this->filterRepository->search($criteria, $context->getContext());
    }

    /**
     * Returns a list with allowed filter names
     *
     * @param array $items
     * @param string $restrictionType
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     * @return array
     * @throws FilterApplyException
     */
    public function filter(array $items, string $restrictionType, ApiRequest $request, SalesChannelContext $context): array
    {
        $config = $this->configService->getByContext($context);

        if ($config->isRestrictionsOverridingTopToDown()) {
            return $this->filterRestrictionsOverridingTopToDown($items, $restrictionType, $config, $request, $context);
        }

        return $this->filterRestrictionsOverridingDownToTop($items, $restrictionType, $config, $request, $context);
    }

    /**
     * Returns a list with allowed filter names when restrictions overridden from top to down
     *
     * @param array $items
     * @param string $restrictionType
     * @param Configuration $config
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     * @return array
     * @throws FilterApplyException
     */
    protected function filterRestrictionsOverridingTopToDown(array $items, string $restrictionType, Configuration $config, ApiRequest $request, SalesChannelContext $context): array
    {
        /** @var FilterRestrictionsCollection $restrictions */
        $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_GLOBAL . '-' . $restrictionType);
        $items = $this->getAllowedItems($items, $restrictionType, $restrictions);

        if ($request instanceof NavigationRequestProduct) {
            /** @var FilterRestrictionsCollection $restrictions */
            $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_NAVIGATION . '-' . $restrictionType);
            $items = $this->getAllowedItems($items, $restrictionType, $restrictions);

            if ($categoryId = $request->getCategoryId()) {
                $items = $this->applyRestrictionsForCategory($items, $categoryId, $config->isRestrictionsParentCategories(), $restrictionType, $context);
            }
        } elseif ($request instanceof ProductSearchRequest) {
            /** @var FilterRestrictionsCollection $restrictions */
            $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_SEARCH . '-' . $restrictionType);
            $items = $this->getAllowedItems($items, $restrictionType, $restrictions);
        }

        return $items;
    }

    /**
     * Returns a list with allowed filter names when restrictions overridden from down to top
     *
     * @param array $items
     * @param string $restrictionType
     * @param Configuration $config
     * @param ApiRequest $request
     * @param SalesChannelContext $context
     * @return array
     * @throws FilterApplyException
     */
    protected function filterRestrictionsOverridingDownToTop(array $items, string $restrictionType, Configuration $config, ApiRequest $request, SalesChannelContext $context): array
    {
        if ($request instanceof NavigationRequestProduct) {
            if ($categoryId = $request->getCategoryId()) {
                $items = $this->applyRestrictionsForCategory($items, $categoryId, $config->isRestrictionsParentCategories(), $restrictionType, $context);
            }

            /** @var FilterRestrictionsCollection $restrictions */
            $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_NAVIGATION . '-' . $restrictionType);
            $items = $this->getAllowedItems($items, $restrictionType, $restrictions);
        } elseif ($request instanceof ProductSearchRequest) {
            /** @var FilterRestrictionsCollection $restrictions */
            $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_SEARCH . '-' . $restrictionType);
            $items = $this->getAllowedItems($items, $restrictionType, $restrictions);
        }

        /** @var FilterRestrictionsCollection $restrictions */
        $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_GLOBAL . '-' . $restrictionType);
        return $this->getAllowedItems($items, $restrictionType, $restrictions);
    }

    /**
     * Returns a list with allowed filter names for category restriction
     *
     * @param array $items
     * @param string $categoryId
     * @param bool $restrictParentCategories
     * @param string $restrictionType
     * @param SalesChannelContext $context
     * @return array
     * @throws FilterApplyException
     */
    protected function applyRestrictionsForCategory(
        array $items,
        string $categoryId,
        bool $restrictParentCategories,
        string $restrictionType,
        SalesChannelContext $context
    ): array
    {
        $categoriesTreeIds = [];
        if ($restrictParentCategories) {
            $maxDeepLevel = 0;
            if ($category = $this->categoryRepository->search(new Criteria([$categoryId]), $context->getContext())->first()) {
                while ($category->getParentId() && $maxDeepLevel < self::MAX_DEEP_CATEGORY) {
                    $categoriesTreeIds[] = $category->getId();
                    /** @var CategoryEntity|null $category */
                    $category = $this->categoryRepository->search(new Criteria([$category->getParentId()]), $context->getContext())
                        ->first();

                    $maxDeepLevel++;
                }

                $categoriesTreeIds[] = $category->getId(); // most top category
            }
            $categoriesTreeIds = array_reverse($categoriesTreeIds);
        } else {
            $categoriesTreeIds[] = $categoryId;
        }

        foreach ($categoriesTreeIds as $categoriesTreeId) {
            /** @var FilterRestrictionsCollection $restrictions */
            $restrictions = $this->getRestrictions($context->getSalesChannelId(), $context->getLanguageId(), $context->getContext(), self::RESTRICTION_NAVIGATION . '-' . $restrictionType, $categoriesTreeId);
            $items = $this->getAllowedItems($items, $restrictionType, $restrictions);
        }

        return $items;
    }

    /**
     * Gets a list with allowed names for one restriction type
     *
     * @param array $items
     * @param string $restrictionType
     * @param FilterRestrictionsCollection $restrictions
     * @return array
     * @throws FilterApplyException
     */
    protected function getAllowedItems(array $items, string $restrictionType, FilterRestrictionsCollection $restrictions): array
    {
        if ($restrictions->count() === 0) {
            return $items;
        }

        if ($restrictions->count() > 2) {
            $this->logger->error(sprintf('More than 2 restrictions exists for %s type', $restrictionType));
            throw new FilterApplyException(sprintf('More than 2 restrictions exists for %s type', $restrictionType));
        }

        $allowAll = false;
        $blockAll = false;
        $allowList = [];
        $blockList = [];
        /** @var FilterRestrictionsEntity $restriction */
        foreach ($restrictions as $restriction) {
            if ($restriction->isAllowed()) {
                $allowAll = $restriction->isAllChecked();
                $allowList = $restriction->getFilters()?->map(fn(FilterEntity $filter) => $filter->getTechnicalName()) ?? [];
            } else {
                $blockAll = $restriction->isAllChecked();
                $blockList = $restriction->getFilters()?->map(fn(FilterEntity $filter) => $filter->getTechnicalName()) ?? [];
            }
        }

        return $this->applyFilter($allowAll, $blockAll, $allowList, $blockList, $items);
    }

    /**
     * Return restrictions
     *
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
        ?string $categoryId = null
    ): EntityCollection
    {
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
    protected function getFilterRestrictionsCriteria(
        ?string $salesChannelId,
        ?string $languageId,
        string $layer,
        ?string $categoryId = null
    ): Criteria
    {
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
            $criteria->addFilter(
                new EqualsFilter('layer', $layer)
            );
        } else {
            $criteria->addFilter(
                new EqualsFilter('layer', $layer)
            );
            $criteria->addFilter(
                new EqualsFilter('isCategory', false)
            );
        }
        return $criteria;
    }

    /**
     * Applies filter restriction for provided names
     *
     * @param bool $allowAll
     * @param bool $blockAll
     * @param array $allowList
     * @param array $blockList
     * @param array $items
     * @return array
     */
    protected function applyFilter(bool $allowAll, bool $blockAll, array $allowList, array $blockList, array $items): array
    {
        $filterItems = [];
        foreach ($items as $item) {
            $allowed = $allowAll || in_array($item, $allowList);
            $blocked = ($blockAll && !in_array($item, $allowList)) || in_array($item, $blockList);
            if ($allowed && !$blocked) {
                $filterItems[] = $item;
            }
        }

        return $filterItems;
    }
}
