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

use Elio\FactFinder\Core\FilterRestrictions\Exception\FilterSyncCreateException;
use Elio\FactFinder\Core\FilterRestrictions\Exception\FilterSyncDeleteException;
use Elio\FactFinder\Core\FilterRestrictions\Exception\FilterSyncUpdateFailedException;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class FilterSyncService
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterSyncService
{
    private EntityRepositoryInterface $propertyRepository;
    private EntityRepositoryInterface $filterRepository;
    private LoggerInterface $logger;

    /**
     * FilterService constructor.
     * @param EntityRepositoryInterface $propertyRepository
     * @param EntityRepositoryInterface $filterRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $propertyRepository,
        EntityRepositoryInterface $filterRepository,
        LoggerInterface $logger
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->filterRepository = $filterRepository;
        $this->logger = $logger;
    }

    /**
     * Sync filter from property for propertyId
     * @param Context $context
     * @param string $propertyId
     */
    public function syncOne(Context $context, string $propertyId): void
    {
        /** @var PropertyGroupEntity $property */
        $property = $this->propertyRepository->search(new Criteria([$propertyId]), $context)->first();

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('propertyId', $property->getId())
        );
        $filter = $this->filterRepository->search($criteria, $context);
        if ($filter->getTotal() > 0) {
            // updating
            /** @var FilterEntity $filterEntity */
            $filterEntity = $filter->first();
            $this->filterRepository->update(
                ['id' => $filterEntity->getId(), 'propertyName' => $property->getName()],
                $context
            );
        } else {
            // creating
            $this->filterRepository->create(
                ['propertyName' => $property->getName(), 'propertyId' => $property->getId(), 'isCustom' => false],
                $context
            );
        }
    }

    /**
     * Sync all properties to filters, sync propertyNames, creating new filters, deleting old filters
     * @param Context $context
     */
    public function syncAll(Context $context): void
    {
        /**
         * Getting all properties
         */
        $properties = $this->propertyRepository->search(new Criteria(), $context);
        $propertiesNames = [];
        $propertiesNamesUpdated = [];
        /** @var PropertyGroupEntity $property */
        foreach ($properties as $property) {
            $propertiesNames[$property->getId()] = $property->getName();
        }

        /**
         * Updating properties names
         */
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsAnyFilter('propertyId', array_keys($propertiesNames))
        );
        $filters = $this->filterRepository->search($criteria, $context);

        $dataToUpdate = [];
        /** @var FilterEntity $filter */
        foreach ($filters as $filter) {
            $dataToUpdate[] = ['id' => $filter->getId(), 'propertyName' => $propertiesNames[$filter->getPropertyId()]];
            $propertiesNamesUpdated[$filter->getPropertyId()] = true;
        }
        try {
            if (count($dataToUpdate) > 0) {
                $this->filterRepository->update($dataToUpdate, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot update filters with this property ids',
                ['$dataToUpdate' => $dataToUpdate]
            );

            throw new FilterSyncUpdateFailedException(
                'FilterSync: Update failed - {{ message }}',
                ['message' => $e->getMessage()]
            );
        }

        /**
         * Deleting old filters
         */
        $criteriaToDelete = new Criteria();
        $criteriaToDelete->addFilter(
            new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('propertyId', array_keys($propertiesNames))])

        );
        $filtersIdsToDelete = $this->filterRepository->searchIds($criteriaToDelete, $context)->getIds();
        $dataToDelete = [];
        foreach ($filtersIdsToDelete as $id) {
            $dataToDelete[] = ['id' => $id];
        }
        try {
            if (count($dataToDelete) > 0) {
                $this->filterRepository->delete($dataToDelete, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot delete filters with this property ids',
                ['$dataToDelete' => $dataToDelete]
            );

            throw new FilterSyncDeleteException(
                'FilterSync: Delete failed - {{ message }}',
                ['message' => $e->getMessage()]
            );
        }

        /**
         * Creating filters for new properties
         */
        $propertyIdsToCreate = array_diff_key($propertiesNames, $propertiesNamesUpdated);
        $dataToCreate = [];
        foreach ($propertyIdsToCreate as $propertyId => $propertyName) {
            $dataToCreate[] = [
                'propertyName' => $propertyName,
                'propertyId' => $propertyId,
                'isCustom' => false
            ];
        }
        try {
            if (count($dataToCreate) > 0) {
                $this->filterRepository->create($dataToCreate, $context);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot create filters with this property ids and names',
                ['$dataToCreate' => $dataToCreate]
            );

            throw new FilterSyncCreateException(
                'FilterSync: Create failed - {{ message }}',
                ['message' => $e->getMessage()]
            );
        }
    }

}