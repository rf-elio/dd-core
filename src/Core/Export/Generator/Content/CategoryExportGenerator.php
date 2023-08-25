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

namespace Elio\ElioSearch\Core\Export\Generator\Content;

use Elio\ElioSearch\Core\Export\ExportConfig;
use Elio\ElioSearch\Core\Export\ExportEntity;
use Elio\ElioSearch\Core\Export\ExportItem;
use Elio\ElioSearch\Core\Export\Generator\Content\ContentExportDefaults as Defaults;
use Elio\ElioSearch\Core\Export\Generator\ExportDefaults;
use Elio\ElioSearch\Core\Export\Generator\Util\ValueUtil;
use Elio\ElioSearch\Core\Export\OutputStream;
use Elio\ElioSearch\Core\Export\SeoRoute;
use Elio\ElioSearch\Core\Util\ArrayUtil;
use Elio\ElioSearch\FactFinder;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CategoryExportGenerator
 * @package Elio\ElioSearch\Core\Export\Generator\Content
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CategoryExportGenerator extends BaseCategoryExportGenerator
{
    protected const EXPORT_TYPE_CATEGORY = 'category';
    protected const EXPORT_TYPE_PAGE = 'page';
    protected array $productInfo = [];

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === ContentExportDefaults::TYPE;
    }

    /**
     * Generates the category export
     *
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        $this->buildCustomFieldInheritance($context->getContext());

        $criteria = $this->getBaseCriteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $export->getBaseCategoryIds()));
        $categories = $this->categoryRepository->search($criteria, $context->getContext());

        $this->processCategories($categories, $export, $output, $context);
    }

    /**
     * Restricts the result to only categories with active products
     *
     * @return Criteria
     */
    protected function getBaseCriteria(): Criteria
    {
        $criteria = parent::getBaseCriteria();
        $criteria->addAssociation('products');
        return $criteria;
    }

    /**
     * @param CategoryEntity $category
     * @param ExportItem $exportItem
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     * @return ExportItem|null
     */
    protected function processCategory(CategoryEntity $category, ExportItem $exportItem, ExportEntity $export, SalesChannelContext $context): ?ExportItem
    {
        $productInformation = null;
        if($category->getProducts() && $category->getProducts()->count() > 0) {
            $productInformation = $this->assembleProductInformation($category->getProducts());

            if($category->getParentId()) {
                ArrayUtil::arrayKeyPush($this->productInfo, $productInformation, $category->getParentId());
            }
        }

        // add product info provided by child categories
        if(isset($this->productInfo[$category->getId()])) {
            $productInformation .= implode(ExportDefaults::KEYWORD_SEPARATOR, $this->productInfo[$category->getId()]);
        }

        $type = $category->getCmsPage() ? $category->getCmsPage()->getType() : self::EXPORT_TYPE_PAGE;
        $keywords = $category->getKeywords() ?? $category->getTranslated()['keywords'];

        // if product category, change type to EXPORT_TYPE_CATEGORY
        if($type === 'product_list' || !empty($productInformation)) {
            $type = self::EXPORT_TYPE_CATEGORY;

            // product categories disabled
            if (!($export->getConfig()[ExportConfig::EXPORT_PRODUCT_CATEGORIES] ?? true)) {
                return null;
            }
        }

        if(!empty($productInformation)) {
            // adding productInformation if not disabled
            if (
                !isset($category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS]) ||
                !$category->getCustomFields()[FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS]
            ) {
                $keywords .= ExportDefaults::KEYWORD_SEPARATOR . ValueUtil::removeDuplicateWords($productInformation);
                $keywords = ltrim($keywords, ExportDefaults::KEYWORD_SEPARATOR);
            }
        }

        $type = ValueUtil::getCustomFieldValue($category->getCustomFields(), FactFinder::CUSTOM_FIELD_CONTENT_EXPORT_TYPE) ?? $type;
        $this->prepareExportItem($category, $exportItem, $type);
        $exportItem->set(Defaults::FIELD_KEYWORDS, ValueUtil::cleanValue($keywords));

        // if this is main navigation category of this salesChannel, we rewrite exported url
        if ($context->getSalesChannel()->getNavigationCategoryId() === $category->getId()) {
            $exportItem->set(Defaults::FIELD_URL, new SeoRoute(
                'frontend.home.page', $category->getId(), []
            ));
        }

        return $exportItem;
    }

    /**
     * Collects all the product information from the current collection as a string
     *
     * @param ProductCollection $products
     * @return string
     */
    protected function assembleProductInformation(ProductCollection $products): string
    {
        $informationCollection = [];
        foreach ($products as $product) {
            if(!$product->getActive()) {
                continue;
            }
            $informationCollection[] = $product->getTranslation('name') ?? $product->getName();
            $informationCollection[] = $product->getTranslation('description') ?? $product->getDescription();
            $informationCollection[] = $product->getProductNumber();
        }
        return implode(ExportDefaults::KEYWORD_SEPARATOR, $informationCollection);
    }
}