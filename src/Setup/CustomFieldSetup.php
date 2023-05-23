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

namespace Elio\FactFinder\Setup;


use RuntimeException;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSetRelation\CustomFieldSetRelationEntity;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomFieldSetup
 *
 * @package Elio\FactFinder\Setup
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CustomFieldSetup
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * PaymentSetup constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates all required custom fields
     *
     * @param array $customFields
     */
    public function install(array $customFields): void
    {
        $this->addCustomFieldSets($customFields);
    }

    /**
     * Deletes custom fields
     *
     * @param array $customFields
     */
    public function uninstall(array $customFields): void
    {
        $this->removeCustomFieldSets($customFields);
    }

    /**
     * @param array $customFields
     */
    private function addCustomFieldSets(array $customFields): void
    {
        $upsets = [];
        foreach ($customFields as $customFieldSetName => $fieldSetConfiguration) {
            $upsert = [
                'id' => $this->getFieldSetId($customFieldSetName),
                'name' => $customFieldSetName,
                'config' => [
                    'label' => [
                        'en-GB' => $customFieldSetName,
                    ]
                ]
            ];

            if (isset($fieldSetConfiguration['label'])) {
                $upsert['config']['label'] = $fieldSetConfiguration['label'];
            }

            $upsert['relations'] = $this->prepareRelations(
                $fieldSetConfiguration['relations'], $upsert['id']
            );
            $upsert['customFields'] = $this->prepareCustomFields(
                $fieldSetConfiguration['fields'], $upsert['id']
            );

            // new fieldset
            if (!$upsert['id']) {
                $upsert['id'] = Uuid::randomHex();
            }

            $upsets[] = $upsert;
        }

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        if (!$customFieldSetRepository) {
            throw new RuntimeException('Service "custom_field_set.repository" not found');
        }

        $customFieldSetRepository->upsert($upsets, new Context(new SystemSource()));
    }

    /**
     * Prepares the relation array for the upsert.
     *
     * @param array $fieldSetRelations
     * @param string|null $fieldSetId
     *
     * @return array
     */
    private function prepareRelations(array $fieldSetRelations, ?string $fieldSetId): array
    {

        $relations = [];
        foreach ($fieldSetRelations as $fieldSetRelation) {
            $relation = ['entityName' => $fieldSetRelation];

            if ($relationId = $this->getFieldSetRelationId($fieldSetId, $fieldSetRelation)) {
                $relation['id'] = $relationId;
            }

            $relations[] = $relation;
        }
        return $relations;
    }

    /**
     * Prepares the custom field
     *
     * @param array $fieldSetFields
     * @param string|null $fieldSetId
     *
     * @return array
     */
    private function prepareCustomFields(array $fieldSetFields, ?string $fieldSetId): array
    {
        $customFields = [];
        $position = 1;
        foreach ($fieldSetFields as $customFieldName => $customFieldProperties) {
            $type = $customFieldProperties['type'];
            $configType = $type;
            $customFieldType = $customFieldProperties['customFieldType'] ?? $type;
            $componentName = $customFieldProperties['componentName'] ?? 'sw-field';
            $label = $customFieldProperties['label'];
            $placeholder = $customFieldProperties['placeholder'] ?? $label;

            if ($configType === 'bool') {
                $configType = 'checkbox';
            }

            $customField = [
                'name' => $customFieldName,
                'type' => $type,
                'config' => [
                    'type' => $configType,
                    'label' => $label,
                    'placeholder' => $placeholder,
                    'componentName' => $componentName,
                    'customFieldType' => $customFieldType,
                    'customFieldPosition' => $position++
                ]
            ];

            if (isset($customFieldProperties['options'])) {
                $customField['config']['options'] = $customFieldProperties['options'];
            }

            if (isset($customFieldProperties['dateType'])) {
                $customField['config']['dateType'] = $customFieldProperties['dateType'];
            }

            if (isset($customFieldProperties['config'])) {
                $customField['config']['config'] = $customFieldProperties['config'];
            }

            if ($customFieldId = $this->getFieldSetFieldId($fieldSetId, $customFieldName)) {
                $customField['id'] = $customFieldId;
            }

            $customFields[] = $customField;
        }

        return $customFields;
    }

    /**
     * Fetches the custom field set id for existing sets
     *
     * @param string $customFieldName
     *
     * @return string|null
     * @noinspection PhpInternalEntityUsedInspection
     */
    private function getFieldSetId(string $customFieldName): ?string
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        if (!$customFieldSetRepository) {
            throw new RuntimeException('Service "custom_field_set.repository" not found');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $customFieldName));
        /** @var CustomFieldSetEntity|null $fieldSet */
        $fieldSet = $customFieldSetRepository->search($criteria, new Context(new SystemSource()))->first();
        return !$fieldSet ? null : $fieldSet->getId();
    }

    /**
     * Fetches the custom field set relation id for existing sets
     *
     * @param string|null $customFieldSetId
     * @param string $relationName
     *
     * @return string|null
     * @noinspection PhpInternalEntityUsedInspection
     */
    private function getFieldSetRelationId(?string $customFieldSetId, string $relationName): ?string
    {
        if (!$customFieldSetId) {
            return null;
        }

        $customFieldSetRelationRepository = $this->container->get('custom_field_set_relation.repository');

        if (!$customFieldSetRelationRepository) {
            throw new RuntimeException('Service "custom_field_set_relation.repository" not found');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFieldSetId', $customFieldSetId));
        $criteria->addFilter(new EqualsFilter('entityName', $relationName));
        /** @var CustomFieldSetRelationEntity|null $fieldSetRelation */
        $fieldSetRelation = $customFieldSetRelationRepository->search($criteria, new Context(new SystemSource()))->first();
        return !$fieldSetRelation ? null : $fieldSetRelation->getId();
    }

    /**
     * Fetches the custom field set relation id for existing sets
     *
     * @param string|null $customFieldSetId
     * @param string $name
     *
     * @return string|null
     * @noinspection PhpInternalEntityUsedInspection
     */
    private function getFieldSetFieldId(?string $customFieldSetId, string $name): ?string
    {
        if (!$customFieldSetId) {
            return null;
        }

        $customFieldRepository = $this->container->get('custom_field.repository');

        if (!$customFieldRepository) {
            throw new RuntimeException('Service "custom_field.repository" not found');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFieldSetId', $customFieldSetId));
        $criteria->addFilter(new EqualsFilter('name', $name));
        /** @var CustomFieldEntity|null $field */
        $field = $customFieldRepository->search($criteria, new Context(new SystemSource()))->first();
        return !$field ? null : $field->getId();
    }

    /**
     * @param array $customFields
     */
    private function removeCustomFieldSets(array $customFields): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $context = new Context(new SystemSource());
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', array_keys($customFields)));

        $result = $customFieldSetRepository->searchIds($criteria, $context);
        $ids = array_map(static fn ($id) => ['id' => $id], $result->getIds());
        if (empty($ids)) {
            return;
        }

        $customFieldSetRepository->delete($ids, $context);
    }
}
