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

use Elio\FactFinder\Configuration\FactFinderConfigService;
use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\Content\ContentExportDefaults as Defaults;
use Elio\FactFinder\Core\Export\Generator\ExportDefaults;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CategoryExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator\Content
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CategoryExportGenerator extends BaseCategoryExportGenerator implements ExportGeneratorInterface
{
    public const TYPE = 'content';
    protected const EXPORT_TYPE_CATEGORY = 'category';
    protected const EXPORT_TYPE_PAGE = 'page';
    protected const SLOT_CONFIG_MAX_LENGTH = 255;
    private SystemConfigService $systemConfigService;

    /**
     * CategoryExportGenerator constructor.
     * @param EntityRepositoryInterface $categoryRepository
     * @param RouterInterface $router
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(EntityRepositoryInterface $categoryRepository, RouterInterface $router, SystemConfigService $systemConfigService)
    {
        parent::__construct($categoryRepository, $router);
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === static::TYPE;
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
        // @todo: move this in export configuration
        $ids = $this->systemConfigService->get(FactFinderConfigService::PLUGIN_CONFIG_PREFIX.'.categoriesToExport', $context->getSalesChannelId());
        $categories = $this->getCategories($ids, $context);
        $this->processCategories($categories, $export, $output, $context);
    }

    /**
     * Restricts the result to only categories with active products
     *
     * @param array $categoryIds
     * @return Criteria
     */
    protected function getCriteria(array $categoryIds): Criteria
    {
        $criteria = parent::getCriteria($categoryIds);
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
        }

        $type = $category->getCmsPage() ? $category->getCmsPage()->getType() : self::EXPORT_TYPE_CMS;
        $description = $category->getDescription();

        if($type === 'product_list' || !empty($productInformation)) {
            $type = self::EXPORT_TYPE_CATEGORY;
            $description = $category->getDescription() . ExportDefaults::KEYWORD_SEPARATOR . ValueUtil::removeDuplicateWords($productInformation);
        }

        $this->prepareExportItem($category, $exportItem, $type);
        $exportItem->set(Defaults::FIELD_DESCRIPTION, ValueUtil::cleanValue($description));
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
            $informationCollection[] = $product->getName();
            $informationCollection[] = $product->getDescription();
            $informationCollection[] = $product->getProductNumber();
        }
        return implode(ExportDefaults::KEYWORD_SEPARATOR, $informationCollection);
    }
}