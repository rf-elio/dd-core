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


use Elio\FactFinder\Core\Export\ExportConfig;
use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\ExportDefaults;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
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
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
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
abstract class BaseCategoryExportGenerator implements ExportGeneratorInterface
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
     * Returns a definition about all fields that are added to the export
     *
     * @param ExportEntity $entity
     * @return array
     */
    public function getModel(ExportEntity $entity): array
    {
        return [
            Defaults::FIELD_ID,
            Defaults::FIELD_TYPE,
            Defaults::FIELD_TITLE,
            Defaults::FIELD_SEO_TEXT,
            Defaults::FIELD_URL,
            Defaults::FIELD_KEYWORDS,
            Defaults::FIELD_DESCRIPTION,
            Defaults::FIELD_IMAGE_URL,
            Defaults::FIELD_PUBLICATION_DATE,
            Defaults::FIELD_PRIORITY,
            Defaults::FIELD_CONTENT_STRUCTURE
        ];
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
     * Loops over each category leven and inherits the custom fields to child categories.
     *
     * Special handling:
     *  - inherited type: The type configured to be inherited to child categories is applied if the child category has
     *                    not an own type configured.
     *
     * @param Node[] $nodes
     */
    private function buildCustomFieldInheritanceByNodes(array $nodes, array $inheritedCustomFields = []): void
    {
        // if we exclude product info in main category we don't inherit its exclusion to child categories
        unset($inheritedCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS]);

        foreach ($nodes as $node) {
            /** @var CategoryEntity $category */
            $category = $node->getValue();
            $categoryCustomFields = $category->getTranslation('customFields') ?? $category->getCustomFields() ?? [];
            $categoryCustomFields = array_filter($categoryCustomFields);

            // add parent custom fields
            $mergedCategoryCustomFields = array_replace($inheritedCustomFields, $categoryCustomFields);

            // apply the category type for child categories to child categories that don't have an own type
            if(
                (
                    !isset($categoryCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE]) ||
                    empty($categoryCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE])
                ) &&
                isset($inheritedCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED]) &&
                !empty($inheritedCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED])
            ) {
                $mergedCategoryCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE] = $mergedCategoryCustomFields[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED];
            }

            $this->customFields[$category->getId()] = $mergedCategoryCustomFields;
            $this->buildCustomFieldInheritanceByNodes($node->getChildNodes(), $mergedCategoryCustomFields);
        }
    }

    /**
     * @param array $categoryIds
     * @param SalesChannelContext $context
     * @return EntityCollection<CategoryEntity>
     * @throws InconsistentCriteriaIdsException
     */
    protected function getChildCategories(array $categoryIds, SalesChannelContext $context): EntityCollection
    {
        if (empty($categoryIds)) {
            return new EntityCollection([]);
        }

        $criteria = $this->getBaseCriteria();
        $this->extendCriteriaForChildCategories($criteria, $categoryIds);
        return $this->categoryRepository->search($criteria, $context->getContext());
    }

    /**
     * Creates the base criteria that is required to load all the categories
     *
     * @return Criteria
     * @throws InconsistentCriteriaIdsException
     */
    protected function getBaseCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addAssociation('cmsPage');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('translations');
        $criteria->addAssociation('media');

        $criteria->addFilter(new EqualsFilter('category.visible', true));
        $criteria->addFilter(new EqualsFilter('category.active', true));
        return $criteria;
    }

    /**
     * Adds the filter for parent ids to the category criteria
     *
     * @param Criteria $criteria
     * @param array $categoryIds
     */
    protected function extendCriteriaForChildCategories(Criteria $criteria, array $categoryIds): void
    {
        $categoryFilters = [];
        foreach ($categoryIds as $categoryId) {
            $categoryFilters[] = new EqualsFilter('parentId', $categoryId);
        }
        $criteria->addFilter(new OrFilter($categoryFilters));
    }

    /**
     * Loops over all given categories and calls the processCategory method. Child categories will be processed
     * recursively.
     *
     * @param EntityCollection $categories
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     * @throws InconsistentCriteriaIdsException
     */
    protected function processCategories(
        EntityCollection $categories,
        ExportEntity $export,
        OutputStream $output,
        SalesChannelContext $context
    ): void {
        $categoryIds = [];
        $processableCategories = [];

        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            // apply prepared custom fields to category
            if (isset($this->customFields[$category->getId()])) {
                $category->setCustomFields($this->customFields[$category->getId()]);
            }

            if (!$this->isCategoryAllowed($category, $export)) {
                continue;
            }

            // child categories are excluded from export -> don't add
            if (
                !(isset($category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]) &&
                $category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED])
            ) {
                $categoryIds[] = $category->getId();
            }

            $processableCategories[] = $category;
        }

        $childCategories = $this->getChildCategories($categoryIds, $context);
        if ($childCategories->count() > 0) {
            $this->processCategories($childCategories, $export, $output, $context);
        }

        /*
         * Child categories need to be processed first, because we inherit down some information to parent
         * categories.
         */
        foreach ($processableCategories as $processableCategory) {
            if ($item = $this->processCategory($processableCategory, new ExportItem(), $export, $context)) {
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
    ): ?ExportItem;

    /**
     * Checks if the given category is allowed to be exported
     *
     * @param CategoryEntity $category
     * @param ExportEntity $export
     * @return bool
     */
    protected function isCategoryAllowed(CategoryEntity $category, ExportEntity $export): bool
    {
        $exportConfig = $export->getConfig();
        $categoryType = $category->getType();

        if (
            ($categoryType === 'link' && !($exportConfig[ExportConfig::EXPORT_LINK_CATEGORIES] ?? true)) ||
            ($categoryType === 'folder' && !($exportConfig[ExportConfig::EXPORT_STRUCTURE_CATEGORIES] ?? true))
        ) {
            return false;
        }

        // category is excluded by parent
        if (
            $category->getParentId() &&
            isset($this->customFields[$category->getParentId()][FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED])
            && $this->customFields[$category->getParentId()][FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED]
        ) {
            return false;
        }

        // category itself is excluded from export
        if (
            isset($category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]) &&
            $category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE]
        ) {
            return false;
        }

        return true;
    }

    /**
     * Creates a default category export item that can later be modified by the specific generator
     *
     * @param CategoryEntity $category
     * @param ExportItem $exportItem
     * @param string $type
     */
    protected function prepareExportItem(CategoryEntity $category, ExportItem $exportItem, string $type): void
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