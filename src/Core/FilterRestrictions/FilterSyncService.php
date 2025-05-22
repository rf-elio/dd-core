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

namespace Elio\ElioDataDiscovery\Core\FilterRestrictions;

use Elio\ElioDataDiscovery\Core\FilterRestrictions\Exception\FilterSyncCreateException;
use Elio\ElioDataDiscovery\Core\FilterRestrictions\Exception\FilterSyncDeleteException;
use Elio\ElioDataDiscovery\Core\FilterRestrictions\Exception\FilterSyncUpdateFailedException;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupTranslation\PropertyGroupTranslationCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class FilterSyncService
 * @package Elio\ElioDataDiscovery\Core\FilterRestrictions
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterSyncService
{
    /**
     * FilterService constructor.
     * @param EntityRepository $propertyRepository
     * @param EntityRepository $filterRepository
     * @param EntityRepository $filterTranslationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly EntityRepository $propertyRepository,
        private readonly EntityRepository $filterRepository,
        private readonly EntityRepository $filterTranslationRepository,
        private readonly LoggerInterface  $logger
    )
    {
    }

    /**
     * Sync filter from property for propertyId
     * @param Context $context
     * @param string $propertyId
     * @param string $type
     */
    public function syncOne(Context $context, string $propertyId, string $type): void
    {
        /** @var PropertyGroupEntity $property */
        $property = $this->propertyRepository->search(new Criteria([$propertyId]), $context)->first();
        $criteria = new Criteria();
        $criteria->addAssociation('translations');
        $criteria->addFilter(new EqualsFilter('type', $type));
        $criteria->addFilter(
            new EqualsFilter('propertyId', $property->getId())
        );
        $propertyTranslations = $property->getTranslations() ?? new PropertyGroupTranslationCollection();
        $filter = $this->filterRepository->search($criteria, $context);
        if ($filter->getTotal() > 0) {
            // updating
            /* @phpstan-ignore-next-line */
            $this->update($filter->first(), $property->getName(), $propertyTranslations->getElements(), $context);
        } else {
            // creating
            if ($property->getName()) {
                $this->create($property->getId(), $property->getName(), $propertyTranslations->getElements(), $type, $context);
            }
        }
    }

    /**
     * Sync all properties to filters, sync labels, creating new filters, deleting old filters
     * @param Context $context
     * @param string $type
     */
    public function syncAll(Context $context, string $type): void
    {
        /**
         * Getting all properties
         */
        $criteria = new Criteria();
        $criteria->addAssociation('translations');
        $properties = $this->propertyRepository->search($criteria, $context);
        $propertiesTranslations = [];
        $propertiesNamesUpdated = [];
        $propertiesNames = [];
        /** @var PropertyGroupEntity $property */
        foreach ($properties as $property) {
            $propertiesTranslations[$property->getId()] = $property->getTranslations()?->getElements();
            $propertiesNames[$property->getId()] = $property->getName();
        }

        /**
         * Updating properties names
         */
        $criteria = new Criteria();
        $criteria->addAssociation('translations');
        $criteria->addFilter(new EqualsFilter('type', $type));
        $criteria->addFilter(
            new EqualsAnyFilter('propertyId', array_keys($propertiesTranslations))
        );
        $filters = $this->filterRepository->search($criteria, $context);
        try {
            /** @var FilterEntity $filter */
            foreach ($filters as $filter) {
                $propertiesNamesUpdated[$filter->getPropertyId()] = true; // flag as updated
                if ($propertiesNames[$filter->getPropertyId()]) {
                    $this->update($filter, $propertiesNames[$filter->getPropertyId()], $propertiesTranslations[$filter->getPropertyId()] ?? [], $context);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot update filters with this property ids',
                ['$filters' => $filters, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
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
        $criteriaToDelete->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('propertyId', array_keys($propertiesTranslations))]));
        $filtersIdsToDelete = $this->filterRepository->searchIds($criteriaToDelete, $context)->getIds();
        try {
            $this->delete($filtersIdsToDelete, $context);
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot delete filters with this ids',
                ['$filtersIdsToDelete' => $filtersIdsToDelete]
            );
            throw new FilterSyncDeleteException(
                'FilterSync: Delete failed - {{ message }}',
                ['message' => $e->getMessage()]
            );
        }

        /**
         * Creating filters for new properties
         */
        $propertyIdsToCreate = array_diff_key($propertiesTranslations, $propertiesNamesUpdated);
        try {
            foreach ($propertyIdsToCreate as $propertyId => $propertyTranslations) {
                if ($propertiesNames[$propertyId]) {
                    $this->create($propertyId, $propertiesNames[$propertyId], $propertyTranslations ?? [], $type, $context);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error(
                'Cannot create filters with this property ids and names',
                ['$propertyIdsToCreate' => $propertyIdsToCreate]
            );
            throw new FilterSyncCreateException(
                'FilterSync: Create failed - {{ message }}',
                ['message' => $e->getMessage()]
            );
        }
    }

    /**
     * Create filters with provided filterNames if they are not existed
     *
     * @param array $filterNames
     * @param Context $context
     * @param bool $isSortingType
     * @return void
     */
    public function createNotExistedFilters(array $filterNames, Context $context, bool $isSortingType = false): void
    {
        $filterNames = array_unique($filterNames);
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('technicalName', $filterNames));

        /** @var FilterCollection $existedFilters */
        $existedFilters = $this->filterRepository->search($criteria, $context)->getEntities();

        $filterNames = array_flip($filterNames);
        foreach ($existedFilters as $existedFilter) {
            unset($filterNames[$existedFilter->getTechnicalName()]);
        }
        $filterNames = array_flip($filterNames);

        $createdFilters = [];
        foreach ($filterNames as $filterName) {
            $filterNameParts = explode($isSortingType ? ':' : '.', (string)$filterName);

            $label = $isSortingType ? implode(' ', $filterNameParts) : $this->decodeFacetName(ucfirst(end($filterNameParts)));

            $createdFilters[] = [
                'id' => Uuid::randomHex(),
                'label' => $label,
                'technicalName' => $filterName,
                'type' => $isSortingType ? FilterEntity::FILTER_TYPE_SORTING : FilterEntity::FILTER_TYPE_FILTER,
                'isCustom' => true,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'label' => $label
                    ]
                ]
            ];
        }

        foreach (array_chunk($createdFilters, 100) as $chunk) {
            $this->filterRepository->create($chunk, $context);
        }
    }

    /**
     * @param string $facetName
     * @return string
     */
    public function decodeFacetName(string $facetName): string
    {
        return preg_replace_callback(
            '/u([0-9a-fA-F]{4})/',
            static function ($matches) {
                return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
            },
            $facetName
        );
    }

    /**
     * Update filter info in database with provided label and propertyTranslations
     * @param FilterEntity $filterEntity
     * @param string $label
     * @param array $propertyTranslations
     * @param Context $context
     */
    private function update(FilterEntity $filterEntity, string $label, array $propertyTranslations, Context $context): void
    {
        $this->filterRepository->update(
            [[
                'id' => $filterEntity->getId(),
                'label' => $label,
                'technicalName' => $label
            ]],
            $context
        );
        $dataToUpdate = [];
        foreach ($propertyTranslations as $propertyTranslation) {
            $dataToUpdate[] = [
                'elioDataDiscoveryFilterId' => $filterEntity->getId(),
                'languageId' => $propertyTranslation->getLanguageId(),
                'label' => $propertyTranslation->getName()
            ];
        }
        if (count($dataToUpdate) > 0) {
            $this->filterTranslationRepository->upsert($dataToUpdate, $context);
        }
    }

    /**
     * Create filter in database with provided propertyId, label and propertyTranslations
     * @param string $propertyId
     * @param string $label
     * @param array $propertyTranslations
     * @param string $type
     * @param Context $context
     */
    private function create
    (
        string  $propertyId,
        string  $label,
        array   $propertyTranslations,
        string  $type,
        Context $context
    ): void
    {
        $newFilterId = Uuid::randomHex();
        $this->filterRepository->create(
            [[
                'id' => $newFilterId,
                'label' => $label,
                'technicalName' => $label,
                'type' => $type,
                'propertyId' => $propertyId,
                'isCustom' => false
            ]],
            $context
        );
        $dataToCreate = [];
        foreach ($propertyTranslations as $propertyTranslation) {
            $dataToCreate[] = [
                'elioDataDiscoveryFilterId' => $newFilterId,
                'languageId' => $propertyTranslation->getLanguageId(),
                'label' => $propertyTranslation->getName()
            ];
        }
        if (count($dataToCreate) > 0) {
            $this->filterTranslationRepository->upsert($dataToCreate, $context);
        }
    }

    /**
     * Removes filters from database
     * @param array $filtersIdsToDelete
     * @param Context $context
     */
    private function delete(array $filtersIdsToDelete, Context $context): void
    {
        $dataToDelete = [];
        foreach ($filtersIdsToDelete as $id) {
            $dataToDelete[] = ['id' => $id];
        }
        if (count($dataToDelete) > 0) {
            $this->filterRepository->delete($dataToDelete, $context);
        }
    }
}
