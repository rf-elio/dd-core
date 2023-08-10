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


/**
 * Class ProductExportDefaults
 * @package Elio\FactFinder\Core\Export\Generator\Product
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
abstract class ProductExportDefaults
{
    public const TYPE = 'product';
    public const FIELD_ID = 'ID';
    public const FIELD_PRODUCT_ID = 'ProductID';
    public const FIELD_MASTER_PRODUCT_NUMBER = 'MasterProductNumber';
    public const FIELD_MANUFACTURER_NUMBER = 'ManufacturerNumber';
    public const FIELD_NAME = 'Name';
    public const FIELD_DESCRIPTION = 'Description';
    public const FIELD_META_TITLE = 'MetaTitle';
    public const FIELD_PRODUCT_URL = 'ProductURL';
    public const FIELD_PRICE = 'Price';
    public const FIELD_RED_PRICE = 'RedPrice';
    public const FIELD_CURRENCY_PRICES = 'CurrencyPrices';
    public const FIELD_MANUFACTURER = 'Manufacturer';
    public const FIELD_CATEGORY_PATH = 'CategoryPath';
    public const FIELD_CATEGORY_IDS = 'CategoryIds';
    public const FIELD_EAN = 'EAN';
    public const FIELD_KEYWORDS = 'Keywords';
    public const FIELD_SEARCH_KEYWORDS = 'SearchKeywords';
    public const FIELD_STOCK = 'Stock';
    public const FIELD_CLOSEOUT = 'Closeout';
    public const FIELD_RATING_AVERAGE = 'RatingAverage';
    public const FIELD_RATING_COUNT = 'RatingCount';
    public const FIELD_SHIPPING_FREE = 'ShippingFree';
    public const FIELD_ATTRIBUTE = 'Attribute';
    public const FIELD_ATTRIBUTE_NON_FILTERABLE = 'AttributeNonFilterable';
    public const FIELD_IMAGE_URL = 'ImageURL';
    public const FIELD_THUMBNAIL_URL = 'ThumbnailURL';
    public const FIELD_TAGS = 'Tags';
    public const FIELD_RELEASE_DATE = 'ReleaseDate';
    public const FIELD_SALES_COUNT = 'SalesCount';
}
