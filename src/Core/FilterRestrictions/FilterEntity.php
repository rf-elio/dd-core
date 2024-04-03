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

use Elio\ElioDataDiscovery\Core\FilterRestrictions\Aggregate\FilterDefinitionTranslation\FilterDefinitionTranslationCollection;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Class FilterEntity
 * @package Elio\ElioDataDiscovery\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterEntity extends Entity
{
    public const FILTER_TYPE_FILTER = 'filter';
    public const FILTER_TYPE_SORTING = 'sorting';

    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * @var string
     */
    protected string $technicalName;
    protected string $type;

    /**
     * @var bool
     */
    protected bool $isCustom;

    /**
     * @var FilterRestrictionsCollection|null
     */
    protected $filterRestrictions;

    /**
     * @var PropertyGroupEntity|null
     */
    protected $property;

    /**
     * @var string
     */
    protected $propertyId;

    /**
     * @var FilterDefinitionTranslationCollection|null
     */
    protected $translations;
    protected bool $displayedByDefault = false;

    /**
     * @return bool
     */
    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    /**
     * @param bool $isCustom
     */
    public function setIsCustom(bool $isCustom): void
    {
        $this->isCustom = $isCustom;
    }

    /**
     * @return mixed
     */
    public function getFilterRestrictions()
    {
        return $this->filterRestrictions;
    }

    /**
     * @param mixed $filterRestrictions
     */
    public function setFilterRestrictions(mixed $filterRestrictions): void
    {
        $this->filterRestrictions = $filterRestrictions;
    }

    /**
     * @return PropertyGroupEntity|null
     */
    public function getProperty(): ?PropertyGroupEntity
    {
        return $this->property;
    }

    /**
     * @param PropertyGroupEntity|null $property
     */
    public function setProperty(?PropertyGroupEntity $property): void
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getPropertyId(): string
    {
        return $this->propertyId;
    }

    /**
     * @param string $propertyId
     */
    public function setPropertyId(string $propertyId): void
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return string
     */
    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    /**
     * @param string $technicalName
     */
    public function setTechnicalName(string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return FilterDefinitionTranslationCollection|null
     */
    public function getTranslations(): ?FilterDefinitionTranslationCollection
    {
        return $this->translations;
    }

    /**
     * @param FilterDefinitionTranslationCollection|null $translations
     */
    public function setTranslations(?FilterDefinitionTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function isDisplayedByDefault(): bool
    {
        return $this->displayedByDefault;
    }

    public function setIsDisplayedByDefault(bool $isDisplayedByDefault): void
    {
        $this->displayedByDefault = $isDisplayedByDefault;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }
}