<?php declare(strict_types=1);
/**
 * Copyright (c) 2020, elio GmbH.
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

namespace Elio\FactFinder\Twig;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Elio\FactFinder\Service\FactFinderProductUpdater;

/**
 * Extend twig with news filters and functions
 *
 * Class TwigProductUpdaterFunctionExtension
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Twig
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class TwigProductUpdaterFunctionExtension extends AbstractExtension
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        UrlGeneratorInterface $generator,
        EntityRepositoryInterface $currencyRepository,
        EntityRepositoryInterface $ruleRepository
    )
    {
        $this->generator = $generator;
        $this->currencyRepository = $currencyRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('clean_value', [$this, 'cleanValue']),
            new TwigFilter('truncate_value', [$this, 'truncateValue']),
            new TwigFilter('utf8_encode', [$this, 'utf8Encode'])
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('productImageUrl', [$this, 'productImageUrl']),
            new TwigFunction('productImageUrl', [$this, 'productImageUrl']),
            new TwigFunction('productPrice', [$this, 'productPrice']),
            new TwigFunction('productCategoryPath', [$this, 'productCategoryPath']),
            new TwigFunction('productAttribute', [$this, 'productAttribute']),
            new TwigFunction('productCustomField', [$this, 'productCustomField']),
            new TwigFunction('productManufacturer', [$this, 'productManufacturer']),
            new TwigFunction('productSearchKeywords', [$this, 'productSearchKeywords']),
        ];
    }

    /**
     * @param $content
     * @return string
     */
    public function cleanValue($content): string
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater(null);
        return $factFinderProductUpdater->cleanValue($content);
    }

    /**
     * @param $content
     * @return string
     */
    public function truncateValue($content)
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater(null);
        return $factFinderProductUpdater->truncate($content);
    }

    /**
     * @param $content
     * @return string
     */
    public function utf8Encode($content): string
    {
        return utf8_encode($content);
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productImageUrl(ProductEntity $product): string
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getImageURL();
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productPrice(ProductEntity $product): string
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);;
        return $factFinderProductUpdater->getPrice();
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productCategoryPath(ProductEntity $product): string
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getCategoryPath();
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productAttribute(ProductEntity $product): string
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getProductAttribute();
    }

    /**
     * @param ProductEntity $product
     * @param string $field
     * @return mixed|string
     */
    public function productCustomField(ProductEntity $product, string $field)
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getProductCustomField($field);
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productManufacturer(ProductEntity $product)
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getManufacturer();
    }

    /**
     * @param ProductEntity $product
     * @return string
     */
    public function productSearchKeywords(ProductEntity $product)
    {
        $factFinderProductUpdater = $this->getFactFinderProductUpdater($product);
        return $factFinderProductUpdater->getSearchKeywords();
    }

    /**
     * @param ProductEntity|null $product
     * @return FactFinderProductUpdater
     */
    private function getFactFinderProductUpdater(?ProductEntity $product): FactFinderProductUpdater
    {
        return new FactFinderProductUpdater(
            $product,
            $this->generator,
            $this->currencyRepository,
            $this->ruleRepository
        );
    }
}
