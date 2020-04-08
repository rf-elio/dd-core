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

namespace Elio\FactFinder\Service;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Shopware\Core\Framework\Context;

/**
 * Updates products according to Fact-finder requirements
 *
 * Class FactFinderProductUpdater
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinderProductUpdater
{
    const SEO_URL_ROUTE_NAME_DETAIL_PAGE = 'frontend.detail.page';
    const THUMBNAIL_SMALL_SIZE = 400;
    const THUMBNAIL_MEDIUM_SIZE = 800;
    const THUMBNAIL_LARGE_SIZE = 1920;

    /**
     * @var ProductEntity
     */
    private $product;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;


    public function __construct(
        ?ProductEntity $product,
        UrlGeneratorInterface $generator,
        EntityRepositoryInterface $currencyRepository
    )
    {
        $this->product = $product;
        $this->generator = $generator;
        $this->currencyRepository = $currencyRepository;
        $this->context = Context::createDefaultContext();
    }

    /**
     * update product properties
     *
     * @return ProductEntity
     */
    public function update():ProductEntity
    {
        $this->setName();
        $this->setDescription();
        $this->setManufacturer();
        $this->setManufacturerNumber();
        $this->setEan();
        $this->setKeywords();

        return $this->product;
    }

    public function getProductURL($schemeRelative = false):string
    {
        return $this->generator->generate(
            self::SEO_URL_ROUTE_NAME_DETAIL_PAGE,
            ['productId' => $this->product->getId()],
            $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function getImageURL():string
    {
        $thumbnails = $this->product->getCover()->getMedia()->getThumbnails()->getElements();
        foreach ($thumbnails as $thumbnail){
          if (
              $thumbnail->getHeight() === self::THUMBNAIL_LARGE_SIZE &&
              $thumbnail->getWidth() === self::THUMBNAIL_LARGE_SIZE
          )
              return $thumbnail->getUrl();
        }
        return "";
    }

    public function getPrice():string
    {
        $resultPrice = '|';
        foreach ($this->product->getPrice()->getElements() as $price)
        {
            $resultPrice .= 'gross='.$price->getGross().'|net='.number_format($price->getNet(), 2, '.', '').'|';
        }

        return $this->cleanValue($resultPrice);
    }

    public function getManufacturer():string
    {
        return $this->cleanValue(
            '|name='
            .$this->product->getManufacturer()->getTranslation('name')
            .'|id='.$this->product->getManufacturer()->getId()
            .'|'
        );
    }

    public function getCategoryPath():string
    {
        $path = '';
        $categories = $this->product->getCategories()->getElements();

        $index = 0;
        $numCategories = count($categories);
        foreach($categories as $category){
            $path .= join('/',array_slice($category->getBreadcrumb(),1));
            if(++$index < $numCategories){
                $path = $path.'|' ;
            }


        }
        return $this->cleanValue($path);
    }

    public function getProductAttribute():string
    {
        $resultAttribute = '|';
        $attributes = $this->product->getProperties()->getElements();
        foreach ($attributes as $attribute){
            $resultAttribute.= $attribute->getGroup()->getName().'='.$attribute->getName().'|';
        }

        return $this->cleanValue($resultAttribute);
    }

    public function getProductCustomField(string $field)
    {
        $customFields = $this->product->getCustomFields();
        return (empty($customFields))? "":$customFields[$field];
    }

    private function setName():void
    {
        $this->product->setName(
            $this->cleanValue($this->product->getName())
        );
    }
    private function setDescription():void
    {
        $this->product->setDescription(
            $this->truncate($this->cleanValue($this->product->getDescription()))
        );
    }

    private function setManufacturer():void
    {
        $this->product->getManufacturer()->setName(
            $this->cleanValue($this->product->getManufacturer()->getName())
        );
    }
    private function setEan():void
    {
        $this->product->setEan(
            $this->cleanValue($this->product->getEan())
        );
    }
    private function setKeywords():void
    {
        $this->product->setKeywords(
            $this->cleanValue($this->product->getKeywords())
        );
    }

    public  function getSearchKeywords():string
    {
        $keywords = [];
        $searchKeywords = $this->product->getSearchKeywords();

        if (empty($searchKeywords))
            return "";

        foreach ($searchKeywords->getElements() as $searchKeyword){
            $keywords[] = $searchKeyword->getKeyword();
        }
        return $this->cleanValue(implode("|", $keywords));
    }

    private function setManufacturerNumber()
    {
        $this->product->setManufacturerNumber(
            $this->cleanValue($this->product->getManufacturerNumber())
        );
    }

    public function cleanValue(?string $value):string
    {
        $value = empty($value)? "":$value;
        return trim(strip_tags($value));
    }

    public function truncate(string $text, int $chars = 900):string
    {
        if (strlen($text) <= $chars) {
            return $text;
        }
        $text = $text." ";
        $text = substr($text,0,$chars);
        $text = substr($text,0,strrpos($text,' '));
        $text = $text."...";

        return $text;
    }

}
