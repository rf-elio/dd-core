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

namespace Elio\FactFinder\Core\Export\Generator\Product;


use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
use Elio\FactFinder\Core\Export\Generator\Product\Event\FilterProductExportItemPrepareEvent;
use Elio\FactFinder\Core\Export\Generator\Product\Event\FilterProductModelEvent;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Elio\FactFinder\Core\Export\SeoRoute;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\ProductPageSeoUrlRoute;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class ProductExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ProductExportGenerator implements ExportGeneratorInterface
{
    private const PRODUCT_CHUNK_SIZE = 500;
    public const TYPE = 'product';
    private EntityRepositoryInterface $productRepository;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * ProductExportGenerator constructor.
     * @param EntityRepositoryInterface $productRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityRepositoryInterface $productRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === self::TYPE;
    }

    /**
     * Defines all fields that should be available in the product export
     *
     * @param ExportEntity $export
     * @return array
     */
    public function getModel(ExportEntity $export): array
    {
        $model = [
            ProductExportDefaults::FIELD_PRODUCT_ID,
            ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER,
            ProductExportDefaults::FIELD_MANUFACTURER_NUMBER,
            ProductExportDefaults::FIELD_NAME,
            ProductExportDefaults::FIELD_DESCRIPTION,
            ProductExportDefaults::FIELD_PRODUCT_URL,
            ProductExportDefaults::FIELD_PRICE,
            ProductExportDefaults::FIELD_MANUFACTURER,
            ProductExportDefaults::FIELD_CATEGORY_PATH,
            ProductExportDefaults::FIELD_EAN,
            ProductExportDefaults::FIELD_KEYWORDS,
            ProductExportDefaults::FIELD_SEARCH_KEYWORDS,
            ProductExportDefaults::FIELD_STOCK,
            ProductExportDefaults::FIELD_RATING_AVERAGE,
            ProductExportDefaults::FIELD_SHIPPING_FREE,
            ProductExportDefaults::FIELD_ATTRIBUTE,
            ProductExportDefaults::FIELD_IMAGE_URL
        ];

        foreach ($export->getMapping() as $mapping) {
            $model[] = $mapping['target'];
        }

        $event = new FilterProductModelEvent($export, $model);
        $this->eventDispatcher->dispatch($event);
        return $event->getModel();
    }

    /**
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('categories');
        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', $export->getSalesChannelId()));
        $criteria->setLimit(self::PRODUCT_CHUNK_SIZE);

        $mappings = $export->getMapping();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $iterator = new RepositoryIterator($this->productRepository, $context->getContext(), $criteria);
        while ($products = $iterator->fetch()) {
            foreach ($products as $product) {
                $item = new ExportItem();
                $this->prepareExportItem($product, $item);
                $this->addMappedPropertiesToExportItem($product, $item, $mappings, $propertyAccessor);
                $this->eventDispatcher->dispatch(new FilterProductExportItemPrepareEvent($product, $item, $export, $context));
                $output->write($item);
            }
        }
    }

    /**
     * Maps the product data into the export item
     *
     * @param ProductEntity $product
     * @param ExportItem $item
     */
    private function prepareExportItem(ProductEntity $product, ExportItem $item): void
    {
        $translated = $product->getTranslated();
        $item->set(ProductExportDefaults::FIELD_PRODUCT_ID, $product->getId());
        $item->set(ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER, $product->getProductNumber());
        $item->set(ProductExportDefaults::FIELD_MANUFACTURER_NUMBER, $product->getManufacturerNumber());
        $item->set(ProductExportDefaults::FIELD_NAME, $product->getName() ?? $translated['name'] ?? '');
        $item->set(ProductExportDefaults::FIELD_DESCRIPTION, $product->getDescription() ?? $translated['description'] ?? '');
        $item->set(ProductExportDefaults::FIELD_PRICE, $product->getPrice()->first()->getGross());

        $manufacturer = $product->getManufacturer();
        if($manufacturer) {
            $item->set(ProductExportDefaults::FIELD_MANUFACTURER, $manufacturer->getTranslation('name') ?? $manufacturer->getName());
        }

        $item->set(ProductExportDefaults::FIELD_CATEGORY_PATH, $this->getCategoryPath($product));
        $item->set(ProductExportDefaults::FIELD_EAN, $product->getEan());
        $item->set(ProductExportDefaults::FIELD_KEYWORDS, $product->getKeywords() ?? $translated['keywords'] ?? '');
        $item->set(ProductExportDefaults::FIELD_SEARCH_KEYWORDS, implode(', ', $product->getSearchKeywords() ?? $translated['customSearchKeywords'] ?? []));
        $item->set(ProductExportDefaults::FIELD_STOCK, $product->getStock());
        $item->set(ProductExportDefaults::FIELD_RATING_AVERAGE, $product->getRatingAverage());
        $item->set(ProductExportDefaults::FIELD_SHIPPING_FREE, $product->getShippingFree());
        $item->set(ProductExportDefaults::FIELD_ATTRIBUTE, $this->getProductAttribute($product));

        if($product->getCover() && $product->getCover()->getMedia()) {
            $item->set(ProductExportDefaults::FIELD_IMAGE_URL, $product->getCover()->getMedia()->getUrl());
        }

        $item->set(ProductExportDefaults::FIELD_PRODUCT_URL, new SeoRoute(
            ProductPageSeoUrlRoute::ROUTE_NAME, $product->getId(), ['productId' => $product->getId()]
        ));
    }

    /**
     * Adds the fields that are defined in the dynamic mapping
     *
     * - supports different levels and Collection::first()
     * - examples: manufacturer.name, price.first.gross
     * - can be extended to provide more options for mapping language
     * @param ProductEntity $product
     * @param ExportItem $item
     * @param array $mappings
     * @param PropertyAccessorInterface $propertyAccessor
     */
    private function addMappedPropertiesToExportItem(
        ProductEntity $product, ExportItem $item, array $mappings, PropertyAccessorInterface $propertyAccessor
    ): void
    {
        foreach ($mappings as $mapping) {
            if (str_contains($mapping['source'], '.')) {
                $parts = explode('.',$mapping['source']);
                $previousObj = $product;
                foreach ($parts as $part) {
                    if ($part === 'first') {
                        $previousObj = array_values($propertyAccessor->getValue($previousObj, 'elements'))[0];
                    } else {
                        $previousObj = $propertyAccessor->getValue($previousObj, $part);
                    }
                }
                $item->set($mapping['target'], $previousObj);
            } else {
                $item->set($mapping['target'], $propertyAccessor->getValue($product, $mapping['source']));
            }
        }
    }

    /**
     * Builds the category path for ff
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getCategoryPath(ProductEntity $product): string
    {
        if(!$product->getCategories()) {
            return '';
        }

        $path = '';
        $categories = $product->getCategories()->getElements();

        $index = 0;
        $numCategories = count($categories);
        foreach ($categories as $category) {
            $path .= implode('/', array_slice($category->getBreadcrumb(), 1));
            if (++$index < $numCategories) {
                $path .= '|';
            }
        }

        return $path;
    }

    /**
     * Appends the product attributes
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getProductAttribute(ProductEntity $product): string
    {
        if(!$product->getProperties()) {
            return '';
        }

        $resultAttribute = '|';
        $attributes = $product->getProperties()->getElements();
        foreach ($attributes as $attribute) {
            $group = $attribute->getGroup();
            if($group) {
                $name = $group->getTranslation('name') ?? $group->getName();
                $value = $attribute->getTranslation('name') ?? $attribute->getName();
                $resultAttribute .= $name . '=' . $value . '|';
            }
        }

        return ValueUtil::cleanValue($resultAttribute);
    }
}