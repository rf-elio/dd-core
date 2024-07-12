<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Api\Search\ResponseTransformer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Elio\ElioDataDiscovery\Api\Response\ResponseCollection;
use Elio\ElioDataDiscovery\Api\Search\Response\ProductListingResponse;
use Elio\ElioDataDiscovery\Api\Transform\ResponseTransformerInterface;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class AbstractProductTransformer
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
abstract class AbstractProductTransformer implements ResponseTransformerInterface
{
    /**
     * AbstractProductTransformer constructor.
     *
     * @param ProductListingLoader $listingLoader
     * @param Connection $connection
     */
    public function __construct(
        private readonly ProductListingLoader $listingLoader,
        private readonly Connection           $connection
    )
    {
    }

    /**
     * @param array $productNumbers
     * @param array $mainNumbers
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @return ProductListingResponse
     */
    //TODO: Rename function
    public function parentTransform(array $productNumbers, array $mainNumbers, ResponseCollection $responseCollection, SalesChannelContext $context): ProductListingResponse
    {
        $productNumberSort = array_flip($productNumbers);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $productNumbers));
        $criteria->addAssociation('manufacturer');

        /** @var ProductCollection $products */
        $products = $this->listingLoader->load($criteria, $context)->getEntities();

        // sorts the product collection based on the original ff result order
        $products->sort(static function (ProductEntity $a, ProductEntity $b) use ($productNumberSort) {
            $aPosition = $productNumberSort[$a->getProductNumber()] ?? 0;
            $bPosition = $productNumberSort[$b->getProductNumber()] ?? 0;
            return $aPosition <=> $bPosition;
        });

        $listing = $responseCollection->get(ProductListingResponse::class) ?? new ProductListingResponse();
        $responseCollection->set(ProductListingResponse::class, $listing);
        $listing->setProducts($products);

        // total count must be corrected by the difference we have for the found products
        $shouldCount = count($mainNumbers);
        $isCount = $products->count();

        $difference = $shouldCount - $isCount;
        $listing->setTotalHits($listing->getTotalHits() - $difference);
        return $listing;
    }

    /**
     * Extracts the main and variant product numbers and ids in the right order from the numbers of the search result
     *
     * @param array<int, string> $mainNumbers
     *
     * @return array<string, array<string, string>>
     * @throws Exception
     */
    protected function extractMainAndVariantProducts(array $mainNumbers): array
    {
        /** @var array<string, array<string, string>> return */
        return $this->connection->fetchAllAssociativeIndexed(
            '# ff product-transformer::extract-product-numbers
            SELECT
                IFNULL(child.product_number, parent.product_number) as number,
                LOWER(HEX(IFNULL(child.id, parent.id))) as id,
                parent.product_number as parentNumber,
                LOWER(HEX(parent.id)) as parentId,
                LOWER(HEX(child.id)) as childId
            FROM product as parent
                LEFT JOIN product as child
                    ON parent.id = child.parent_id
                    AND parent.version_id = child.version_id
            WHERE parent.product_number in (:numbers)
            ORDER BY FIELD(parent.product_number, :numbers), child.product_number
            ',
            [
                'numbers' => $mainNumbers
            ],
            ['numbers' => Connection::PARAM_STR_ARRAY]
        );
    }
}
