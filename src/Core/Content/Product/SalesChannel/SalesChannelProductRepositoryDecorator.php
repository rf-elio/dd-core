<?php
/**
 * Copyright (c) 2022, elio GmbH.
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

namespace Elio\FactFinder\Core\Content\Product\SalesChannel;


use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class SalesChannelProductRepositoryDecorator
 * @package Elio\FactFinder\Core\Content\Product\SalesChannel
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2022, elio GmbH (https://www.elio-systems.com)
 */
class SalesChannelProductRepositoryDecorator implements SalesChannelRepositoryInterface
{
    private SalesChannelRepositoryInterface $decorated;

    public function __construct(SalesChannelRepositoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function search(Criteria $criteria, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        $this->removeFactFinderSortingFromCriteria($criteria);
        return $this->decorated->search($criteria, $salesChannelContext);
    }

    public function aggregate(Criteria $criteria, SalesChannelContext $salesChannelContext): AggregationResultCollection
    {
        $this->removeFactFinderSortingFromCriteria($criteria);
        return $this->decorated->aggregate($criteria, $salesChannelContext);
    }

    public function searchIds(Criteria $criteria, SalesChannelContext $salesChannelContext): IdSearchResult
    {
        $this->removeFactFinderSortingFromCriteria($criteria);
        return $this->decorated->searchIds($criteria, $salesChannelContext);
    }

    /**
     * Removes ff sortings that cannot work with shopware
     *
     * @param Criteria $criteria
     * @return void
     */
    protected function removeFactFinderSortingFromCriteria(Criteria $criteria) : void
    {
        $sortingList = $criteria->getSorting();
        $criteria->resetSorting();
        foreach ($sortingList as $sorting) {
            $fieldName = $sorting->getField();

            if (!str_starts_with($fieldName, '_')) {
                $criteria->addSorting($sorting);
            }
        }
    }
}
