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

namespace Elio\FactFinder\Core\Export\Generator;


use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\OutputStream;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

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
    public const TYPE = 'product';
    private EntityRepositoryInterface $productRepository;
    private RouterInterface $router;

    /**
     * ProductExportGenerator constructor.
     * @param EntityRepositoryInterface $productRepository
     * @param RouterInterface $router
     */
    public function __construct(EntityRepositoryInterface $productRepository, RouterInterface $router)
    {
        $this->productRepository = $productRepository;
        $this->router = $router;
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
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('categories');
        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', $export->getSalesChannelId()));

        // @todo: don't fetch all products (memory) use some pagination stuff
        $products = $this->productRepository->search($criteria, $context->getContext());

        // @todo: translations
        // @todo: attributes or products fields by configuration
        // @todo: extensibility
        /** @var ProductEntity $product */
        foreach ($products as $product) {
            $item = new ExportItem();
            $item->set('ProductID', $product->getId());
            $item->set('MasterProductNumber', $product->getProductNumber());
            $item->set('ManufacturerNumber', $product->getManufacturerNumber());
            $item->set('Name', $product->getName());
            $item->set('Description', $product->getDescription());
            $item->set('ProductURL', $product->getId());
            $item->set('Price', $product->getPrice()->first()->getGross());
            $item->set('Manufacturer', $product->getManufacturer()->getName());
            $item->set('CategoryPath', $this->getCategoryPath($product));
            $item->set('EAN', $product->getId());
            $item->set('Keywords', $product->getKeywords());
            $item->set('SearchKeywords', $product->getSearchKeywords());
            $item->set('Stock', $product->getStock());
            $item->set('RatingAverage', $product->getRatingAverage());
            $item->set('ShippingFree', $product->getShippingFree());
            $item->set('Attribute', $this->getProductAttribute($product));

            // @todo: check if this is the main image
            if($product->getMedia() && $product->getMedia()->first() && $product->getMedia()->first()->getMedia()) {
                $item->set('ImageURL', $product->getMedia()->first()->getMedia()->getUrl());
            }

            // @todo: rewrite url
            $item->set(
                'ProductURL',
                $this->router->generate('frontend.detail.page', ['productId' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            );

            $output->write($item);
        }
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    private function getCategoryPath(ProductEntity $product): string
    {
        if(!$product->getCategories()) {
            return '';
        }

        $path = '';
        $categories = $product->getCategories()->getElements();

        $index = 0;
        $numCategories = count($categories);
        foreach ($categories as $category) {
            $path .= join('/', array_slice($category->getBreadcrumb(), 1));
            if (++$index < $numCategories) {
                $path .= '|';
            }
        }

        return $path;
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    private function getProductAttribute(ProductEntity $product): string
    {
        if(!$product->getProperties()) {
            return '';
        }

        $resultAttribute = '|';
        $attributes = $product->getProperties()->getElements();
        foreach ($attributes as $attribute) {
            $resultAttribute .= $attribute->getGroup()->getName() . '=' . $attribute->getName() . '|';
        }

        return $this->cleanValue($resultAttribute);
    }

    /**
     * @param string|null $value
     * @return string
     */
    private function cleanValue(?string $value): string
    {
        $value = empty($value) ? "" : $value;
        return trim(strip_tags($value));
    }
}