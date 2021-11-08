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
use Elio\FactFinder\Core\Export\Generator\ExportDefaults;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Elio\FactFinder\Core\Export\SeoRoute;
use Elio\FactFinder\Core\Util\Tree\Node;
use Elio\FactFinder\Core\Util\Tree\RandomAddTree;
use Elio\FactFinder\FactFinder;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\NavigationPageSeoUrlRoute;
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
    protected array $customFields = [];

    /**
     * CategoryExportGenerator constructor.
     * @param EntityRepositoryInterface $categoryRepository
     */
    public function __construct(EntityRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Creates a tree by all categories. The tree will be looped over to generate the custom field inheritance
     *
     * @param Context $context
     */
    protected function buildCustomFieldInheritance(Context $context): void
    {
        $criteria = new Criteria();
        $categories = $this->categoryRepository->search($criteria, $context);
        $tree = new RandomAddTree();

        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            $tree->add($category->getId(), $category->getParentId(), $category);
        }

        $this->buildCustomFieldInheritanceByNodes($tree->create());
    }

    /**
     * Loops over each category leven and inherits the custom fields to child categories
     *
     * @param Node[] $nodes
     */
    private function buildCustomFieldInheritanceByNodes(array $nodes, array $inheritedCustomFields = []) : void
    {
        foreach ($nodes as $node) {
            /** @var CategoryEntity $category */
            $category = $node->getValue();
            $categoryCustomFields = $category->getCustomFields() ?? [];
            $categoryCustomFields = array_filter($categoryCustomFields);
            $categoryCustomFields = array_replace($inheritedCustomFields, $categoryCustomFields);
            $this->customFields[$category->getId()] = $categoryCustomFields;
            $this->buildCustomFieldInheritanceByNodes($node->getChildNodes(), $categoryCustomFields);
        }
    }

    /**
     * @param array $categoryIds
     * @param SalesChannelContext $context
     * @return EntityCollection<CategoryEntity>
     */
    protected function getCategories(array $categoryIds, SalesChannelContext $context): EntityCollection
    {
        if(empty($categoryIds)) {
            return new EntityCollection([]);
        }

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

        $categoryFilters = [];
        foreach ($categoryIds as $categoryId) {
            $categoryFilters[] = new EqualsFilter('parentId', $categoryId);
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
     * @param EntityCollection $categories
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    protected function processCategories(EntityCollection $categories, ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        $categoryIds = [];

        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            if(isset($this->customFields[$category->getId()])) {
                $category->setCustomFields($this->customFields[$category->getId()]);
            }

            if(
                isset($category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]) &&
                $category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]
            ) {
                continue;
            }

            $categoryIds[] = $category->getId();
            if($item = $this->processCategory($category, new ExportItem(), $export, $context)) {
                $output->write($item);
            }
        }

        $childCategories = $this->getCategories($categoryIds, $context);
        if($childCategories->count() > 0) {
            $this->processCategories($childCategories, $export, $output, $context);
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
        $translated = $category->getTranslated();
        $exportItem->set(Defaults::FIELD_ID, $category->getId());
        $exportItem->set(Defaults::FIELD_TYPE, $type);
        $exportItem->set(Defaults::FIELD_TITLE, ValueUtil::cleanValue($category->getName() ?? $translated['name']));
        $exportItem->set(Defaults::FIELD_SEO_TEXT, ValueUtil::cleanValue($category->getMetaDescription() ?? $translated['metaDescription']));
        $exportItem->set(Defaults::FIELD_URL, new SeoRoute(
            NavigationPageSeoUrlRoute::ROUTE_NAME, $category->getId(), ['navigationId' => $category->getId()]
        ));
        $exportItem->set(Defaults::FIELD_KEYWORDS, ValueUtil::cleanValue($category->getKeywords() ?? $translated['keywords']));
        $exportItem->set(Defaults::FIELD_DESCRIPTION, ValueUtil::cleanValue($category->getDescription() ?? $translated['description']));
        $exportItem->set(Defaults::FIELD_IMAGE_URL, '');
        if($category->getMedia()){
            $exportItem->set(Defaults::FIELD_IMAGE_URL, ValueUtil::cleanValue($category->getMedia()->getUrl()));
        }
        $exportItem->set(Defaults::FIELD_PUBLICATION_DATE, ValueUtil::cleanValue($category->getCreatedAt()->format(ExportDefaults::DATE_TIME_FORMAT)));
        $exportItem->set(Defaults::FIELD_PRIORITY, ValueUtil::getCustomFieldValue($category->getCustomFields(), FactFinder::CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY) ?? Defaults::DEFAULT_PRIORITY);
        $exportItem->set(Defaults::FIELD_CONTENT_STRUCTURE, ValueUtil::cleanValue(implode('/', array_slice($category->getBreadcrumb(), 1))));
    }
}