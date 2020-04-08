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

namespace Elio\FactFinder\Components\Helper;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 *
 * Class FactFinderHelper
 *
 * @category  Helper
 * @package   Shopware\Plugins\FactFinder\Components\Helper
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class FactFinderHelper
{
    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(SalesChannelRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Converts Fact-Finder records to shopware products
     *
     * @param SalesChannelContext $context
     * @param Criteria $criteria
     * @param array $records
     * @return EntitySearchResult
     */
    public function convertRecords(SalesChannelContext $context, Criteria $criteria, array $records): EntitySearchResult
    {
        $ids = [];

        foreach ($records as $record){
            $ids[] = $record['id'];
        }
        $criteria->setIds($ids);

        return $this->productRepository->search($criteria, $context);
    }

    /**
     * Concatenates elements with the given operator
     *
     * @param string $operator
     * @param array $elements
     * @return string
     */
    public function concatenateElements(string $operator, array $elements = []): string
    {
        if (count($elements) === 0)
            return "";

        if (count($elements) === 1)
            return $elements[0];

        $concatenated = "";
        $index = 0;
        foreach ($elements as $element){
            ++$index ;
            $concatenated .= ($index === count($elements)) ? $element : $element . $operator;
        }

        return $concatenated;
    }
}
