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


use Elio\FactFinder\Core\Defaults;
use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\ExportDefaults;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
use Elio\FactFinder\Core\Export\Generator\Product\Event\FilterProductExportItemPrepareEvent;
use Elio\FactFinder\Core\Export\Generator\Product\Event\FilterProductModelEvent;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Elio\FactFinder\Core\Export\SeoRoute;
use Elio\FactFinder\Core\Features\FeatureServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\CountResult;
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
    private const PRODUCT_THUMBNAIL_SIZE = 200;
    private EntityRepositoryInterface $productRepository;
    private EventDispatcherInterface $eventDispatcher;
    private EntityRepositoryInterface $salesChannelRepository;
    private FeatureServiceInterface $featureService;

    /**
     * ProductExportGenerator constructor.
     * @param EntityRepositoryInterface $productRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param FeatureServiceInterface $featureService
     */
    public function __construct(
        EntityRepositoryInterface $productRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $salesChannelRepository,
        FeatureServiceInterface $featureService
    )
    {
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->featureService = $featureService;
    }

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === ProductExportDefaults::TYPE;
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
            ProductExportDefaults::FIELD_ID,
            ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER,
            ProductExportDefaults::FIELD_PRODUCT_ID,
            ProductExportDefaults::FIELD_MANUFACTURER_NUMBER,
            ProductExportDefaults::FIELD_NAME,
            ProductExportDefaults::FIELD_DESCRIPTION,
            ProductExportDefaults::FIELD_META_TITLE,
            ProductExportDefaults::FIELD_PRODUCT_URL,
            ProductExportDefaults::FIELD_PRICE,
            ProductExportDefaults::FIELD_RED_PRICE,
            ProductExportDefaults::FIELD_MANUFACTURER,
            ProductExportDefaults::FIELD_CATEGORY_PATH,
            ProductExportDefaults::FIELD_CATEGORY_IDS,
            ProductExportDefaults::FIELD_EAN,
            ProductExportDefaults::FIELD_KEYWORDS,
            ProductExportDefaults::FIELD_SEARCH_KEYWORDS,
            ProductExportDefaults::FIELD_STOCK,
            ProductExportDefaults::FIELD_CLOSEOUT,
            ProductExportDefaults::FIELD_RATING_AVERAGE,
            ProductExportDefaults::FIELD_RATING_COUNT,
            ProductExportDefaults::FIELD_SHIPPING_FREE,
            ProductExportDefaults::FIELD_ATTRIBUTE,
            ProductExportDefaults::FIELD_ATTRIBUTE_NON_FILTERABLE,
            ProductExportDefaults::FIELD_IMAGE_URL,
            ProductExportDefaults::FIELD_THUMBNAIL_URL,
            ProductExportDefaults::FIELD_TAGS,
            ProductExportDefaults::FIELD_RELEASE_DATE,
            ProductExportDefaults::FIELD_SALES_COUNT,
        ];

        if($this->featureService->getContext()->isEnabled('currency-specific-prices')) {
            $model[] = ProductExportDefaults::FIELD_CURRENCY_PRICES;
        }

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
     * @throws InconsistentCriteriaIdsException
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        // add currencies for price calculation
        $criteria = new Criteria([$context->getSalesChannelId()]);
        $criteria->addAssociation('currencies');

        if($salesChannel = $this->salesChannelRepository->search($criteria, $context->getContext())->first()) {
            $context->getSalesChannel()->setCurrencies($salesChannel->getCurrencies());
        }

        // fetch products
        $criteria = new Criteria();
        $criteria->addAssociation('manufacturer.media');
        $criteria->addAssociation('visibilities');
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('tags');
        $criteria->addFilter(new EqualsFilter('product.active', true));
        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', $export->getSalesChannelId()));
        $criteria->setLimit(self::PRODUCT_CHUNK_SIZE);

        $mappings = $export->getMapping();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $iterator = new RepositoryIterator($this->productRepository, $context->getContext(), $criteria);
        while ($products = $iterator->fetch()) {
            /** @var ProductEntity $product */
            foreach ($products as $product) {
                // @todo: performance!
                $criteria = new Criteria([$product->getId()]);
                $criteria->addAggregation(new CountAggregation('rating-count', 'product.productReviews.id'));
                $result = $this->productRepository->search($criteria, $context->getContext());
                /** @var CountResult|null $ratingCountAggregation */
                $ratingCountAggregation = $result->getAggregations()->get('rating-count');
                $ratingCount = $ratingCountAggregation ? $ratingCountAggregation->getCount() : 0;

                $item = new ExportItem();
                $this->prepareExportItem(
                    $product,
                    $item,
                    $ratingCount,
                    $context
                );
                $this->addMappedPropertiesToExportItem($product, $item, $mappings, $propertyAccessor);
                $event = new FilterProductExportItemPrepareEvent($product, $item, $export, $context);
                $this->eventDispatcher->dispatch($event);
                if ($event->isExclude()) {
                    continue;
                }
                $output->write($item);
            }
        }
    }

    /**
     * Maps the product data into the export item
     *
     * @param ProductEntity $product
     * @param ExportItem $item
     * @param int $ratingCount
     * @param SalesChannelContext $context
     */
    private function prepareExportItem(ProductEntity $product, ExportItem $item, int $ratingCount, SalesChannelContext $context): void
    {
        $parentProduct = null;
        if($product->getParentId()) {
            $parentProduct = $this->productRepository->search(new Criteria([$product->getParentId()]), $context->getContext())->first();
        }

        $parentProduct = $parentProduct ?? $product;
        $translated = $product->getTranslated();
        $item->set(ProductExportDefaults::FIELD_ID, $product->getId());
        $item->set(ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER, $parentProduct->getProductNumber());
        $item->set(ProductExportDefaults::FIELD_PRODUCT_ID, $product->getProductNumber());
        $item->set(ProductExportDefaults::FIELD_MANUFACTURER_NUMBER, $product->getManufacturerNumber());
        $item->set(ProductExportDefaults::FIELD_NAME, $product->getName() ?? $translated['name'] ?? '');
        $item->set(ProductExportDefaults::FIELD_DESCRIPTION, ValueUtil::cleanValue($product->getDescription() ?? $translated['description'] ?? ''));
        $item->set(ProductExportDefaults::FIELD_META_TITLE, ValueUtil::cleanValue($product->getMetaTitle() ?? $translated['metaTitle']));

        [$price, $redPrice] = $this->getProductPrice($product) ?? [null, null];
        $item->set(ProductExportDefaults::FIELD_PRICE, ValueUtil::formatPrice($price));
        $item->set(ProductExportDefaults::FIELD_RED_PRICE, ValueUtil::formatPrice($redPrice));
        if($this->featureService->getContext()->isEnabled('currency-specific-prices')) {
            $item->set(ProductExportDefaults::FIELD_CURRENCY_PRICES, $this->getProductPrices($product, $context));
        }

        $manufacturer = $product->getManufacturer();
        if($manufacturer) {
            $item->set(ProductExportDefaults::FIELD_MANUFACTURER, $manufacturer->getTranslation('name') ?? $manufacturer->getName());
        }
        $item->set(ProductExportDefaults::FIELD_CATEGORY_PATH, $this->getCategoryPath($product));
        $item->set(ProductExportDefaults::FIELD_CATEGORY_IDS, $this->getCategoryIds($product));
        $item->set(ProductExportDefaults::FIELD_EAN, $product->getEan());
        $item->set(ProductExportDefaults::FIELD_KEYWORDS, $product->getKeywords() ?? $translated['keywords'] ?? '');
        $item->set(ProductExportDefaults::FIELD_SEARCH_KEYWORDS, implode(', ', $product->getSearchKeywords() ?? $translated['customSearchKeywords'] ?? []));
        $item->set(ProductExportDefaults::FIELD_STOCK, $product->getStock());
        $item->set(ProductExportDefaults::FIELD_CLOSEOUT, $product->getIsCloseout() ? 1 : 0);
        $item->set(ProductExportDefaults::FIELD_RATING_AVERAGE, $product->getRatingAverage());
        $item->set(ProductExportDefaults::FIELD_RATING_COUNT, $ratingCount);
        $item->set(ProductExportDefaults::FIELD_SHIPPING_FREE, $product->getShippingFree());
        $item->set(ProductExportDefaults::FIELD_ATTRIBUTE, $this->getProductAttribute($this->getFilterableProductProperties($product)));
        $item->set(ProductExportDefaults::FIELD_ATTRIBUTE_NON_FILTERABLE, $this->getProductAttribute($this->getNonFilterableProductProperties($product)));
        $item->set(ProductExportDefaults::FIELD_TAGS, $this->getProductTags($product));
        $item->set(ProductExportDefaults::FIELD_SALES_COUNT, $product->getSales());
        $item->set(
            ProductExportDefaults::FIELD_RELEASE_DATE,
            $product->getReleaseDate() ? $product->getReleaseDate()->format(ExportDefaults::DATE_TIME_FORMAT) : ''
        );

        if($product->getCover() && $product->getCover()->getMedia()) {
            $item->set(ProductExportDefaults::FIELD_IMAGE_URL, $product->getCover()->getMedia()->getUrl());
            $item->set(ProductExportDefaults::FIELD_THUMBNAIL_URL, $this->getThumbnailUrl($product->getCover()->getMedia()->getThumbnails()));
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
    protected function addMappedPropertiesToExportItem(
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
                    } elseif (is_object($previousObj) || is_array($previousObj)) {
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
            $path .= implode('/', array_map('rawurlencode', array_slice($category->getBreadcrumb(), 1)));
            if (++$index < $numCategories) {
                $path .= Defaults::VALUE_SEPARATOR;
            }
        }

        return $path;
    }

    /**
     * Builds the category path for ff
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getCategoryIds(ProductEntity $product): string
    {
        if(!$product->getCategories()) {
            return '';
        }

        $productCategoryIds = [];
        $categories = $product->getCategories()->getElements();

        foreach ($categories as $category) {
            $path = $category->getPath();
            $ids = explode('|', $path);
            $ids = array_filter($ids);
            $productCategoryIds[] = implode('/', $ids);
        }

        return implode(Defaults::VALUE_SEPARATOR, $productCategoryIds);
    }

    /**
     * Appends the product attributes
     *
     * @param array<PropertyGroupOptionEntity> $properties
     * @return string
     */
    protected function getProductAttribute(array $properties): string
    {
        $resultAttribute = Defaults::VALUE_SEPARATOR;
        foreach ($properties as $property) {
            $group = $property->getGroup();
            if($group !== null) {
                $name = $group->getTranslation('name') ?? $group->getName();
                $value = $property->getTranslation('name') ?? $property->getName();
                $resultAttribute .= $name . '=' . $value . Defaults::VALUE_SEPARATOR;
            }
        }

        return ValueUtil::cleanValue($resultAttribute);
    }

    /**
     * @param ProductEntity $product
     * @return array<PropertyGroupOptionEntity>
     */
    protected function getFilterableProductProperties(ProductEntity $product): array
    {
        if ($product->getProperties() === null) {
            return [];
        }
        return $product->getProperties()->filter(
            static fn (PropertyGroupOptionEntity $option) => $option->getGroup() !== null && $option->getGroup()->getFilterable()
        )->getElements();
    }

    /**
     * @param ProductEntity $product
     * @return array<PropertyGroupOptionEntity>
     */
    protected function getNonFilterableProductProperties(ProductEntity $product): array
    {
        if ($product->getProperties() === null) {
            return [];
        }
        return $product->getProperties()->filter(
            static fn (PropertyGroupOptionEntity $option) => $option->getGroup() !== null && !$option->getGroup()->getFilterable()
        )->getElements();
    }

    /**
     * Creates the product tags string
     *
     * @param ProductEntity $product
     * @return string
     */
    protected function getProductTags(ProductEntity $product) : string
    {
        if(!$product->getTags()) {
            return '';
        }

        $tags = [];
        foreach ($product->getTags() as $tag) {
            $tags[] = $tag->getTranslation('name') ?? $tag->getName();
        }

        return implode(Defaults::VALUE_SEPARATOR, $tags);
    }

    /**
     * Fetches the main product price
     *
     * @param ProductEntity $product
     * @return array|null
     */
    protected function getProductPrice(ProductEntity $product) : ?array
    {
        if ($product->getPrice() === null || !$price = $product->getPrice()->first()) {
            return null;
        }

        $redPrice = null;
        if (
            $price->getListPrice() &&
            $price->getListPrice()->getGross() &&
            $price->getListPrice()->getGross() > $price->getGross()
        ) {
            $redPrice = $price->getListPrice()->getGross();
        }

        return [$price->getGross(), $redPrice];
    }

    /**
     * Fetches the product price string with all currencies
     *
     * @param ProductEntity $product
     * @param SalesChannelContext $context
     *
     * @return string
     */
    protected function getProductPrices(ProductEntity $product, SalesChannelContext $context) : string
    {
        [$price] = $this->getProductPrice($product) ?? [null];
        if (!$price) {
            return '';
        }

        $prices = [];
        foreach ($context->getSalesChannel()->getCurrencies() as $currency) {
            $currencyPrice = $price;
            if ($currency->getId() !== $context->getCurrency()->getId()) {
                $currencyPrice *= $currency->getFactor();
            }

            $prices[] = sprintf(
                '%s~~%s=%s',
                $currency->getIsoCode(),
                $currency->getSymbol(),
                ValueUtil::formatPrice($currencyPrice)
            );
        }

        return !empty($prices) ? sprintf(
            '%s%s%s',
            Defaults::VALUE_SEPARATOR,
            implode(Defaults::VALUE_SEPARATOR, $prices),
            Defaults::VALUE_SEPARATOR
        ) : '';
    }

    /**
     * Searches for the best matching thumbnail
     *
     * @param MediaThumbnailCollection|null $thumbnailCollection
     * @return string
     */
    protected function getThumbnailUrl(?MediaThumbnailCollection $thumbnailCollection): string
    {
        if (!$thumbnailCollection || $thumbnailCollection->count() <= 0) {
            return '';
        }

        $targetSize = self::PRODUCT_THUMBNAIL_SIZE;
        $bestMatching = null;
        $bestMatchingSizeDifference = 0;
        foreach ($thumbnailCollection as $thumbnail) {
            $targetSizeDifference = abs($targetSize - $thumbnail->getWidth());
            if (!$bestMatching || $targetSizeDifference < $bestMatchingSizeDifference) {
                $bestMatching = $thumbnail;
                $bestMatchingSizeDifference = $targetSizeDifference;
            }
        }

        return $bestMatching ? $bestMatching->getUrl() : '';
    }
}
