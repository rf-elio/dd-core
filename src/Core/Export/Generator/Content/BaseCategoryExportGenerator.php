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

namespace Elio\FactFinder\Core\Export\Generator\Content;


use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\NavigationPageSeoUrlRoute;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Elio\FactFinder\Core\Export\Generator\Content\ContentExportDefaults as Defaults;

/**
 * Class BaseCategoryExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator\Content
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseCategoryExportGenerator
{
    protected EntityRepositoryInterface $categoryRepository;
    protected RouterInterface $router;

    /**
     * CategoryExportGenerator constructor.
     * @param EntityRepositoryInterface $categoryRepository
     * @param RouterInterface $router
     */
    public function __construct(EntityRepositoryInterface $categoryRepository, RouterInterface $router)
    {
        $this->categoryRepository = $categoryRepository;
        $this->router = $router;
    }

    /**
     * @param array $categoryIds
     * @param SalesChannelContext $context
     * @return EntitySearchResult<CategoryEntity>
     */
    protected function getCategories(array $categoryIds, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = $this->getCriteria($categoryIds);
        return $this->categoryRepository->search($criteria, $context->getContext());
    }

    /**
     * @param array $categoryIds
     * @return Criteria
     */
    protected function getCriteria(array $categoryIds): Criteria
    {
        $criteria = new Criteria();
        $criteria->addAssociation('cmsPage');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('translations');
        $criteria->addAssociation('media');
        //$criteria->addAssociation('cmsPage.sections');
        //$criteria->addAssociation('cmsPage.sections.blocks');
        //$criteria->addAssociation('cmsPage.sections.blocks.slots');
        $criteria->addAssociation('cmsPage.sections.blocks.slots.translations');

        $categoryFilters = [];
        foreach ($categoryIds as $categoryId) {
            $categoryFilters[] = new ContainsFilter('path', $categoryId);
        }

        $criteria->addFilter(new OrFilter($categoryFilters));
        $criteria->addFilter(new EqualsFilter('category.visible', true));
        $criteria->addFilter(new EqualsFilter('category.active', true));
        return $criteria;
    }

    /**
     * Loops over all given categories and calls the processCategory method. Child categories will be processed
     * recursively.
     *
     * @param EntitySearchResult $categories
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    protected function processCategories(EntitySearchResult $categories, ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            if($item = $this->processCategory($category, new ExportItem(), $export, $context)) {
                $output->write($item);
            }
        }
    }

    /**
     * This method should be used to do the category processing
     *
     * @param CategoryEntity $category
     * @param ExportItem $exportItem
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     */
    abstract protected function processCategory(
        CategoryEntity $category,
        ExportItem $exportItem,
        ExportEntity $export,
        SalesChannelContext $context
    ) : ?ExportItem;

    /**
     * Creates a default category export item that can later be modified by the specific generator
     *
     * @param CategoryEntity $category
     * @param ExportItem $exportItem
     * @param string $type
     */
    protected function prepareExportItem(CategoryEntity $category, ExportItem $exportItem, string $type) : void
    {
        $exportItem->set(Defaults::FIELD_ID, $category->getId());
        $exportItem->set(Defaults::FIELD_TYPE, $type);
        $exportItem->set(Defaults::FIELD_TITLE, ValueUtil::cleanValue($category->getName()));
        $exportItem->set(Defaults::FIELD_SEO_TEXT, ValueUtil::cleanValue($category->getMetaDescription()));
        $exportItem->set(
            Defaults::FIELD_URL,
            $this->router->generate(
                NavigationPageSeoUrlRoute::ROUTE_NAME,
                ['navigationId' => $category->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
        $exportItem->set(Defaults::FIELD_KEYWORDS, ValueUtil::cleanValue($category->getKeywords()));
        $exportItem->set(Defaults::FIELD_DESCRIPTION, ValueUtil::cleanValue($category->getDescription()));
        if($category->getMedia()){
            $exportItem->set('ImageUrl', ValueUtil::cleanValue($category->getMedia()->getUrl()));
        }
        $exportItem->set(Defaults::FIELD_PUBLICATION_DATE, ValueUtil::cleanValue($category->getCreatedAt()->format('Y-m-d H:i:s')));
        $exportItem->set(Defaults::FIELD_PRIORITY, Defaults::DEFAULT_PRIORITY);
        $exportItem->set(Defaults::FIELD_CONTENT_STRUCTURE, ValueUtil::cleanValue(join('/', array_slice($category->getBreadcrumb(), 1))));
    }
}