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

namespace Elio\FactFinder\Core\FilterRestrictions;

use DateTime;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Throwable;

/**
 * Class FilterService
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterService
{

    private EntityRepositoryInterface $propertyRepository;
    private EntityRepositoryInterface $filterRepository;
    private EntityRepositoryInterface $salesChannelRepository;
    private LoggerInterface $logger;
    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    /**
     * FilterService constructor.
     * @param AbstractSalesChannelContextFactory $salesChannelContextFactory
     * @param EntityRepositoryInterface $propertyRepository
     * @param EntityRepositoryInterface $filterRepository
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractSalesChannelContextFactory $salesChannelContextFactory,
        EntityRepositoryInterface $propertyRepository,
        EntityRepositoryInterface $filterRepository,
        EntityRepositoryInterface $salesChannelRepository,
        LoggerInterface $logger
    ) {
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->propertyRepository = $propertyRepository;
        $this->filterRepository = $filterRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->logger = $logger;
    }

    public function syncOne(string $salesChannelId, Context $context, string $propertyId)
    {
        //todo: implement method
    }

    public function syncAll(string $salesChannelId, Context $context)
    {
        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $this->salesChannelRepository->search(new Criteria([$salesChannelId]), $context)->first();

        $properties = $this->propertyRepository->search(new Criteria(), $context);

        $propertiesNames = [];
        /** @var PropertyGroupEntity $property */
        foreach ($properties as $property) {
            $propertiesNames[$property->getId()] = $property->getName();
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('propertyId', array_keys($propertiesNames))
        );
        $filters = $this->filterRepository->search($criteria, $context);

        //array_keys($propertiesNames);
        var_dump($filters->getTotal());
        die();


        if (!$salesChannel || !$salesChannel->getDomains()) {
            $this->logger->info(
                'Cannot find SalesChannelEntity with this id',
                ['$salesChannelId' => $salesChannelId]
            );

            throw new RuntimeException(
                sprintf(
                    'Cannot find SalesChannelEntity with this id: "%s"',
                    $salesChannelId
                )
            );
        }

        $languageIds = $salesChannel->getDomains()->map(function (SalesChannelDomainEntity $salesChannelDomain) {
            return $salesChannelDomain->getLanguageId();
        });


        foreach ($languageIds as $languageId) {
            $salesChannelContext = $this->salesChannelContextFactory->create(
                '',
                $salesChannel->getId(),
                [SalesChannelContextService::LANGUAGE_ID => $languageId]
            );
        }
    }
}